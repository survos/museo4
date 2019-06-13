Amplitude = require('amplitudejs');
Foundation = require('foundation');

console.log('in player');


/*
Amplitude.addSong({
    "name": "Song Name 1",
    "artist": "Artist Name",
    "album": "Album Name",
    "url": "/song/url.mp3",
    "cover_art_url": "/cover/art/url.jpg"
});
console.log(Amplitude.getSongs());
*/

let songs = [];
$.get("/exhibits-feed.json", function(data) {
    // Amplitude.init({debug: true, "songs": []});
    data.forEach(function (d) {
        let song = {
            artist: d.title,
            album: d.description,
            name: d.filename,
            cover_art_url: "https://dummyimage.com/800x240/000/fff&text=foto+de+" + d.code,
            url: d.s3Url
        };
        songs.push(song);

        if (false) {
            Amplitude.addSong(song);
            console.log(Amplitude.getSongs());
        }

    });

    Amplitude.init({
        default_playlist_art: 'https://dummyimage.com/100x50/444/fff&text=' + 'playlist',
        continue_next: false,
        preload: 'metadata',
        // debug: true,
        "songs": songs
    });
    /*
    console.log(songs, Amplitude.getSongs());
    if (0)
     */
});

// Amplitude.addPlaylist('x', {}, {});


