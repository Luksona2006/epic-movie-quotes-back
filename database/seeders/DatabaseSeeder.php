<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\MovieGenre;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create();
        Genre::factory()->create([
            'name' => [
                'en' => 'Drama',
                'ka' => 'დრამა'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Thriller',
                'ka' => 'თრილერი'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Horror',
                'ka' => 'საშინელებათა'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Comedy',
                'ka' => 'კომედია'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Action',
                'ka' => 'მძაფრსიუჟეტიანი'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Western',
                'ka' => 'ვესტერნი'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Science fiction',
                'ka' => 'სამეცნიერო ფანტასტიკა'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'History',
                'ka' => 'ისტორიული'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Fantasy',
                'ka' => 'ფანტასტიკური'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Mystery',
                'ka' => 'მისტიური'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Historical fiction',
                'ka' => 'ისტორიული მხატვრული'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Romance novel',
                'ka' => 'რომანტიული რომანი'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Fiction',
                'ka' => 'მხატვრული'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Adventure',
                'ka' => 'სათავგადასავლო'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Criminal fiction',
                'ka' => 'კრიმინალური მხატვრული'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Narrative',
                'ka' => 'ნარატიული'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Speculative fiction',
                'ka' => 'სპეკულაციური მხატვრული'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Action fiction',
                'ka' => 'მძაფრსიუჟეტიანი მხატვრული'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Romance',
                'ka' => 'რომანტიკული'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Romantic comedy',
                'ka' => 'რომანტიკული კომედია'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Isekai',
                'ka' => 'ისეკაი'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Hybrid genre',
                'ka' => 'ჰიბრიდული ჟანრის'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Humor',
                'ka' => 'იუმორისტული'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Dark comedy',
                'ka' => 'შავი იუმორი'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Historical drama',
                'ka' => 'ისტორიული დრამა'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Magical Realism',
                'ka' => 'ჯადოსნური რეალიზმი'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Psychological thriller',
                'ka' => 'ფსიქოლოგიური თრილერი'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Comedy horror',
                'ka' => 'საშინელებათა კომედია'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Coming-of-age story',
                'ka' => 'ასაკობრივი'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Slapstick',
                'ka' => 'სლაპსტიკური იუმორი'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Melodrama',
                'ka' => 'მელოდრამა'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Screenplay',
                'ka' => 'სცენარის'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Biographical',
                'ka' => 'ბიოგრაფიული'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Suspense',
                'ka' => 'შეჩერებული'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Fantasy fiction',
                'ka' => 'ფანტასტიკური მხატვრული'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'High fantasy',
                'ka' => 'მაღალი ფანტაზიის'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Fairy tale',
                'ka' => 'ზღაპარი'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Cyberpunk',
                'ka' => 'კიბერპანკი'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Farce',
                'ka' => 'ფარსი'
            ]
        ]);

        Genre::factory()->create([
            'name' => [
                'en' => 'Apocalyptic and post-apocalyptic fiction',
                'ka' => 'აპოკალიფსური და პოსტ-აპოკალიფსური მხატვრული'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Supernatural',
                'ka' => 'ზებუნებრივი'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Anime',
                'ka' => 'ანიმე'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Manga',
                'ka' => 'მანგა'
            ]
        ]);
        Genre::factory()->create([
            'name' => [
                'en' => 'Satire',
                'ka' => 'სატირული'
            ]
        ]);
        Movie::factory()->create();
        Quote::factory()->create();
        MovieGenre::create(['movie_id' => 1, 'genre_id' => 1]);
    }
}
