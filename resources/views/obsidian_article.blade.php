<x-app-layout>
    <x-slot name="header">
        @if($isIndex)
            <h2>Index of <strong>{{ config('app.name') }}</strong></h2>
        @else
            <h2>{{ $article->name }}</h2>
        @endif
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-700 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-gray-700">
                    {!! $article->getParsed() !!}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
