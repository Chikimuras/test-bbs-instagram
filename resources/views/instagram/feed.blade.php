<div class=".gap .grid .grid-cols-1.sm grid-cols-2.md grid-cols-3.lg grid-cols-4.xl grid-cols-5">
    @foreach ($media as $post)
        <div class="media-container">
            <a class="media-link" href="{{ $post['permalink'] }}" target="_blank">
                <img class="media-content" src="{{ $post['media_url'] }}" alt="{{ $post['caption'] }}">
            </a>
            <p class="media-caption">{{ $post['caption'] }} by {{ $post['username'] }}</p>
        </div>
    @endforeach
</div>
