@section('title', 'Clubs Non Approuvés')

<x-app-layout>
    <x-club-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-6" />

        <div class="text-2xl font-semibold text-center mb-8">{{ __('Clubs Non Approuvés') }}</div>

        @if($clubs->isEmpty())
            <div class="alert alert-info text-center">
                {{ __('Aucun club non approuvé trouvé.') }}
            </div>
        @else
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 text-left">{{ __('Titre') }}</th>
                        <th class="py-2 text-left">{{ __('Description') }}</th>
                        <th class="py-2 text-left">{{ __('Propriétaire') }}</th>
                        <th class="py-2 text-left">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clubs as $club)
                        <tr class="border-b">
                            <td class="py-2">{{ $club->title }}</td>
                            <td class="py-2">{{ Str::limit($club->description, 50) }}</td>
                            <td class="py-2">{{ $club->owner->name }}</td>
                            <td class="py-2">
                                <form action="{{ route('admin.club.approve', $club->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="bg-green-500 text-white py-1 px-3 rounded">{{ __('Approuver') }}</button>
                                </form>
                                <form action="{{ route('club.delete', $club->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 text-white py-1 px-3 rounded">{{ __('Supprimer') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-6">
                {{ $clubs->links() }} <!-- Pagination links -->
            </div>
        @endif
    </x-club-card>
</x-app-layout>
