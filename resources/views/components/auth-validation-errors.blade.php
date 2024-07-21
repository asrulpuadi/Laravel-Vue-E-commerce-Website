@props(['errors'])

@if ($errors->any())
    <div {{$attributes->merge(['class'=>'bg-red-600 rounded py-3 px-4 text-white'])}}>
        <div class="font-medium">
            {{__('Whoops! Something went wrong.')}}
        </div>
        <ul class="mt-3 list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{$error}}</li>
            @endforeach

        </ul>
    </div>
@endif