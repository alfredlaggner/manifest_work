@extends('layouts.app')
@section('title', 'Driver Logs')
@section('content')
    <div class="container">

        @php

            if (! $saleinvoice->quantity_corrected)
            {
                $saleinvoice_corrected = $saleinvoice->quantity;
            }
            else
            {
                $saleinvoice_corrected = $saleinvoice->quantity_corrected;
            }

        @endphp
        <div class="card" style="width: 100%">
            <div class="card-body">
                <div class="card-header">
                    <h5 class="card-subtitle mb-2 text-muted text-center">{{$saleinvoice->name}}</h5>
                    <h6 class="card-title text-center">Change Amount Delivered</h6>
                </div>
                <form method="post" action="{{route('saleinvoices.update', $id)}}">
                    @csrf
                    <input name="_method" type="hidden" value="PATCH">
                    <input type="hidden" name="saleinvoice_id" value="{{$saleinvoice->id}}">
                    <input type="hidden" name="log_id" value="{{$log_id}}">
                    <input type="hidden" name="order_id" value="{{$saleinvoice->order_id}}">

                    <div class="form-group">
                        <label for="quantity_corrected">Original Amount</label>
                        <input type="text" class="form-control" readonly value="{{$saleinvoice->quantity}}">
                    </div>
                    <div class="form-group">
                        <label for="quantity_corrected">Edit Amount</label>
                        <input type="text" class="form-control" name="quantity_corrected"
                               value="{{$saleinvoice_corrected}}">
                    </div>
                    <div class="form-group">
                        <label for="make">Reasons:</label>
                        <select multiple="multiple" class="form-control" name="note_id[]"  size="6">
                            @foreach ($line_notes as $note)
                                @if (in_array ($note->id,$selected_notes))
                                    <option value="{{$note->id}}" selected>{{$note->id}}
                                        - {{$note->note}}   </option>
                                @else
                                    <option value="{{$note->id}}">{{$note->id}} - {{$note->note}}   </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="note">Comment:</label>
                        <textarea class="form-control" name="note" id="note" rows="3"> {{$saleinvoice->note}}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="form-group col-md-4" style="margin-top:60px">
                            <button type="submit" class="btn btn-success" style="margin-left:38px">Save</button>
                            <a href="{{ route('go-home') }}" class="btn btn-outline-primary btn-sm" role="button"
                               aria-pressed="true">Home</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection