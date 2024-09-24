@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Clubs non approuvés</h1>

    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Titre</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clubs as $club)
                <tr>
                    <td>{{ $club->title }}</td>
                    <td>{{ $club->description }}</td>
                    <td>
                        <form action="{{ route('admin.club.approve', $club->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success">Approuver</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $clubs->links() }} <!-- Pagination links -->
</div>
@endsection
