@section('title', 'Page d\'accueil - Gestion de club')

<x-app-layout>
    <div class="relative min-h-screen bg-dots-darker bg-center bg-gray-100 selection:bg-red-500 selection:text-white">
        <div class="flex flex-wrap p-6">
            @if (!empty($clubs->all()))
                @foreach ($clubs as $club)
                    @if ($club->is_approved == true)
                        <div class="w-full sm:w-1/2 lg:w-1/4 p-2">
                            <a href="{{ route("club.show", ["id" => $club->id]) }}" class="block rounded border overflow-hidden hover:shadow-lg transition duration-300 ease-in-out">
                                <div class="p-4 flex flex-col items-center">
                                    <h3 class="text-xl font-semibold text-indigo-600 mb-2 hover:text-indigo-800">{{ $club->title }}</h3>
                                    <p class="text-gray-700 mb-2 h-20 overflow-clip w-3/4 text-ellipsis line-clamp-4">{{ $club->description }}</p>
                                    <div class="flex justify-center items-center mb-2">
                                        <img src="{{ asset("storage/clubs/images/" . basename($club->image)) }}" alt="" class="rounded-lg max-w-full max-h-48">
                                    </div>
                                    <p class="text-gray-600 font-bold">Chef de club: {{ $club->owner->username }}</p>
                                </div>
                            </a>
                        </div>
                    @endif
                @endforeach
            @else
                <p class="text-gray-700 text-center w-full">Aucun club disponible pour le moment.</p>
            @endif
        </div>
    </div>
</x-app-layout>
