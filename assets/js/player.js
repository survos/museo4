Amplitude = require('amplitudejs');

console.log('in player');

Amplitude.init({
    "songs": [
        {
            "name": "Song Name 1",
            "artist": "Artist Name",
            "album": "Album Name",
            "url": "/song/url.mp3",
            "cover_art_url": "/cover/art/url.jpg"
        },
        {
            "name": "Song Name 2",
            "artist": "Artist Name",
            "album": "Album Name",
            "url": "/song/url.mp3",
            "cover_art_url": "/cover/art/url.jpg"
        },
        {
            "name": "Song Name 3",
            "artist": "Artist Name",
            "album": "Album Name",
            "url": "/song/url.mp3",
            "cover_art_url": "/cover/art/url.jpg"
        }
    ]
});

