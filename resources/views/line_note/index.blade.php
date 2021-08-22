@extends('layouts.app')
@section('title', 'Line Notes')
@section('content')
    <div class="container">
        <body>
        <div class="container">
            <br/>
            @if (\Session::has('success'))
                <div class="alert alert-success">
                    <p>{{ \Session::get('success') }}</p>
                </div><br/>
            @endif
            <h3 class="text-center"> Line Notes</h3>
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Notes</th>
                    <th colspan="2">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($line_notes as $note)
                    <tr>
                        <td>{{$note['id']}}</td>
                        <td>{{$note['note']}}</td>

                        <td><a href="{{action('LineNoteController@edit', $note['id'])}}"
                               class="btn btn-warning">Edit</a></td>
                        <td>
                            <form action="{{action('LineNoteController@destroy', $note['id'])}}" method="post">
                                @csrf
                                <input name="_method" type="hidden" value="DELETE">
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <form action="{{action('LineNoteController@create')}}" method="get">
                @csrf
                <input name="_method" type="hidden" value="CREATE">
                <button class="btn btn-primary" type="submit">Add note</button>
                <a href="{{ route('go-home') }}" class="btn btn-outline-primary btn-sm" role="button"
                   aria-pressed="true">Home</a>
            </form>

        </div>
        </body>
        </html>
@endsection