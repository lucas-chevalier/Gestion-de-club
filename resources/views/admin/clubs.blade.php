@section('title', 'Clubs - Admin')
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Liste des Clubs') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4">
                @foreach ($clubs as $club)
                    <div class="bg-white p-6 rounded-md shadow-md flex items-center mb-4">
                        <div class="flex-grow">
                            <h3 class="text-lg font-semibold">{{ $club->title }}</h3>
                            <p class="text-gray-600">
                                Propriétaire: {{ $club->owner->username }}
                            </p>
                            <p class="text-gray-600">
                                Membres: {{ $club->team->allUsers()->count() }}
                            </p>
                        </div>
                        <a href="{{ route('club.show', ['id' => $club->id]) }}" class="inline-flex items-center px-4 py-2 border bg-white border-blue-500 text-blue-500 rounded-md hover:bg-blue-100 hover:text-blue-500 focus:shadow-outline-green transition duration-300 ease-in-out">
                            Voir le club
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $clubs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
