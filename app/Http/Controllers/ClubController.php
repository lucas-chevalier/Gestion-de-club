<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\TeamJoinRequest;
use App\Models\TeamUser;
use Faker\Factory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Team;
use App\Models\User;
use App\Models\Status;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Mockery\Exception;
use Intervention\Image\Image;

class ClubController extends Controller
{
    /**
     * Create a club
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function create(Request $request): RedirectResponse
    {
        try {
            $validator = Validator::make($request->all(), Club::$rules, Club::$messages);

            if ($validator->fails()) {
                return abort(Response::HTTP_FORBIDDEN);
            }

            $image = $this->isImage($request);
            $club = Club::create([
                'title' => $request->title,
                'description' => $request->description,
                'image' => (isset($image)) ? 'storage/clubs/images/' . $image : "storage/clubs/images/default.png",
                'owner_id' => Auth::id(),
                'status_id' => $request->status_id,
            ]);

            $team = Team::create([
                'name' => $request->title,
                'personal_team' => 1,
                'user_id' => Auth::id(),
                'club_id' => $club->id
            ]);

            $tags = $request->input('tags');
            $tags = explode(',', $tags);

            foreach ($tags as $tag) {
                Tag::create(['name' => $tag, 'club_id' => $club->id]);
            }

            return to_route('club.show', ['id' => $club->id]);
        } catch (\Exception $e) {
            Log::error("Impossible de créer le projet ou l'équipe : " . $e->getMessage());
            return redirect()->back()->dangerBanner(
                __('Une erreur s\'est produite lors de la création du projet.'),
            );
        }
    }

    /**
     * Delete a club
     *
     * @param int $clubId
     * @return RedirectResponse
     */
    public function delete(int $clubId): RedirectResponse|Response
    {
        try {
            $team = Team::where('club_id', $clubId)->firstorFail();
            if (Gate::denies('delete-club', $team)) {
                abort(403);
            }

            Club::destroy($clubId);
            Team::where('club_id', $clubId)->delete();
            Tag::where('club_id', $clubId)->delete();

            return to_route('home');
        } catch (ModelNotFoundException $e) {
            Log::error("Le projet à supprimer n'a pas été trouvé : " . $e->getMessage());
            return response()->view('errors.404', [], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error("Impossible de supprimer le projet :" . $e->getMessage());
            return redirect()->back()->dangerBanner(
                __('Une erreur s\'est produite lors de la suppression du projet.'),
            );
        }
    }


    /**
     * Retrieve a unique club
     *
     * @param int $id
     * @return View|RedirectResponse
     */
    public function show(int $id): View|RedirectResponse|Response
    {
        try {
            $club = Club::findOrFail($id);

            $isAlreadyJoinRequest = TeamJoinRequest::where('user_id', Auth::id())->where('team_id', $club->team->id)->first();

            return view('club.show',[
                'club' => $club,
                'users' => $club->team->users,
                'owner' => $club->owner,
                'team' => $club->team,
                'isAlreadyJoinRequest' => $isAlreadyJoinRequest,
                'tags' => $club->tags
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error("Impossible de récupérer les informations du projet : " . $e->getMessage());
            return response()->view('errors.404', [], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error("Une erreur s'est produite lors de la récupération du projet : " . $e->getMessage());
            return redirect()->back()->dangerBanner(
                __('Une erreur s\'est produite lors de la récupération du projet.'),
            );
        }
    }

    /**
     * Retrieve all clubs
     *
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        $clubs = Club::all();

        return view('welcome', [
            'clubs' => $clubs
        ]);
    }

    /**
     * Update the club
     *
     * @param Request $request
     * @param int $clubId
     * @return RedirectResponse
     */
    public function update(Request $request, int $clubId): RedirectResponse|Response
    {
        try {
            $team = Team::where('club_id', $clubId)->firstorFail();
            $club = club::findOrFail($clubId);
            $image = $this->isImage($request);
            $oldTags = Tag::all()->where('club_id', $club->id)->pluck('name')->toArray();
            $newTags = explode(',', $request->tags);

            if (Gate::denies('update-club', $team)) {
                abort(403);
            }

            $validator = Validator::make($request->all(), Club::$rules, Club::$messages);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $tagsToDelete = array_diff($oldTags, $newTags);
            $tagsToCreate = array_diff($newTags, $oldTags);

            Tag::whereIn('name', $tagsToDelete)->delete();

            foreach ($tagsToCreate as $tagName) {
                if (!empty($tagName)) {
                    Tag::create(['name' => $tagName, 'club_id' => $clubId]);
                }
            }

            $club->update([
                'title' => $request->title,
                'description' => $request->description,
                'image' => (isset($image)) ? 'storage/clubs/images/' . $image : $club->image,
                'status_id' => $request->status_id
            ]);

            $team->update([
                'name' => $request->title
            ]);

            return redirect()->route('club.show', ['id' => $club->id]);
        } catch (ModelNotFoundException $e) {
            Log::error("Impossible de trouver le projet à modifer :" . $e->getMessage());
            return response()->view('errors.404', [], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Impossible de modifier le projet : ' . $e->getMessage());
            return redirect()->back()->dangerBanner(
                __('Une erreur s\'est produite lors de la modification du projet.'),
            );
        }
    }

    /**
     *  Redirect to update form
     *
     * @param int $clubId
     * @return View|RedirectResponse
     */
    public function updateForm(int $clubId): View|RedirectResponse|Response
    {
        try {

            $club = Club::findOrFail($clubId);
            $statuses = Status::all();
            $team = Team::where('club_id', $clubId)->firstOrFail();
            $tags = Tag::all()->where('club_id', $club->id)->pluck('name')->toArray();

            $tags = implode(',', $tags);

            if (Gate::denies('update-club', ['team' => $team])) {
                abort(403);
            }

            return view('club.update', [
                'club' => $club,
                'statuses' => $statuses,
                'tags' => $tags
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('Le projet n\'a pas été trouvé : ' . $e->getMessage());
            return response()->view('errors.404', [], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Le projet n\'a pas été trouvé : ' . $e->getMessage());
            return redirect()->back()->dangerBanner(
                __('Une erreur s\'est produite lors de la récupération du projet.'),
            );
        }
    }

    /**
     * Store the image of the club in the storage
     *
     * @param Request $request
     * @return string|null
     */
    private function isImage(Request $request): string|null
    {
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $image = $request->file('image');

            // Générer un nom unique pour le fichier image
            $imageName = uniqid('image_') . '.' . $image->getClientOriginalExtension();

            $manager = new ImageManager(
                new \Intervention\Image\Drivers\Gd\Driver()
            );
            // Redimensionnement de l'image avant de la stocker
            $resizedImage = $manager->read($image)->resize(500, 500);

            // Stocker le fichier image redimensionné dans le répertoire "public/clubs/images"
            Storage::put('public/clubs/images/' . $imageName, $resizedImage->encode());

            return $imageName;
        } else {
            return null;
        }
    }
}
