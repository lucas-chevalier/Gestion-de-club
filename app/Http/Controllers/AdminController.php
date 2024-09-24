<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\WithPagination;


class AdminController extends Controller
{
    use WithPagination;
    public function listUsers(): View|RedirectResponse
    {
        $users = User::paginate(10);

        return view('admin.users', [
            'users' =>$users,
        ]);
    }

    public function listBannedUsers(): View|RedirectResponse
    {
        $users = User::where('banned', 1)->paginate(10);

        return view('admin.users-banned', [
            'users' => $users,
        ]);
    }

    public function listclubs(): View|RedirectResponse
    {
        $clubs = Club::paginate(10);

        return view('admin.clubs', [
            'clubs' => $clubs,
        ]);
    }

    public function dashboard(): View|RedirectResponse
    {
        $clubs = Club::paginate(5);
        $users = User::paginate(5);

        return view('admin.dashboard', [
            'clubs' => $clubs,
            'users' => $users,
        ]);
    }

    public function ban(Request $request, int $userId): RedirectResponse
    {
        $user = User::find($userId);

        $user->update([
            'banned' => true,
            'banned_until' => $request->date ? $request->date : null
        ]);

        return redirect()->back()->with(['message' => 'L\'utilisateur a bien été banni.']);
    }

    public function unban(int $userId): RedirectResponse
    {
        $user = User::find($userId);

        $user->update([
            'banned' => false,
            'banned_until' => null
        ]);

        return redirect()->back()->with(['message' => 'L\'utilisateur a bien été débanni.']);
    }

    public function delete(int $userId): RedirectResponse
    {
        $user = User::find($userId);

        $user->delete();

        return redirect()->back()->with(['message' => 'L\'utilisateur a bien été supprimé.']);
    }
}
