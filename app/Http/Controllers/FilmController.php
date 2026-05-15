<?php

namespace App\Http\Controllers;

use App\Services\ToadFilmService;
use Illuminate\Http\Request;

class FilmController extends Controller
{
    private ToadFilmService $filmService;

    public function __construct(ToadFilmService $filmService)
    {
        $this->middleware('auth');
        $this->filmService = $filmService;
    }

    public function index(Request $request)
    {
        $perPage = 20;
        $page    = $request->get('page', 1);
        $offset  = ($page - 1) * $perPage;

        $films = $this->filmService->getAllFilms($perPage, $offset);
        $total = $this->filmService->getCountFilms();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $films ?? [],
            $total,
            $perPage,
            $page,
            ['path' => $request->url()]
        );

        return view('films.index', ['films' => $paginator]);
    }

    public function show($id)
    {
        $film = $this->filmService->getFilmById($id);

        if (!$film) {
            abort(404, 'Film non trouvé');
        }

        return view('films.show', [
            'film'            => $film,
            'languages'       => $this->getLanguages(),
            'specialFeatures' => $this->getSpecialFeatures(),
        ]);
    }

    public function create()
    {
        return view('films.create', [
            'languages'       => $this->getLanguages(),
            'specialFeatures' => $this->getSpecialFeatures(),
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string|max:1000',
            'releaseYear'     => 'required|integer|min:1900|max:2100',
            'languageId'      => 'required|integer|min:1',
            'length'          => 'nullable|integer|min:1|max:500',
            'rating'          => 'nullable|string|in:G,PG,PG-13,R,NC-17',
            'specialFeatures' => 'nullable|array',
            'specialFeatures.*' => 'string',
            'rentalDuration'  => 'required|integer|min:1|max:30',
            'rentalRate'      => 'required|numeric|min:0',
            'replacementCost' => 'required|numeric|min:0',
        ], [
            'title.required'          => 'Le titre est obligatoire.',
            'title.max'               => 'Le titre ne peut pas dépasser 255 caractères.',
            'releaseYear.required'    => 'L\'année de sortie est obligatoire.',
            'releaseYear.min'         => 'L\'année doit être supérieure ou égale à 1900.',
            'releaseYear.max'         => 'L\'année ne peut pas dépasser 2100.',
            'languageId.required'     => 'La langue est obligatoire.',
            'rating.in'               => 'La note doit être G, PG, PG-13, R ou NC-17.',
            'rentalDuration.required' => 'La durée de location est obligatoire.',
            'rentalRate.required'     => 'Le tarif de location est obligatoire.',
            'replacementCost.required'=> 'Le coût de remplacement est obligatoire.',
        ]);

        // Toad attend une chaîne séparée par des virgules, pas un tableau
        if (!empty($validatedData['specialFeatures'])) {
            $validatedData['specialFeatures'] = implode(',', $validatedData['specialFeatures']);
        }

        // le champ s'appelle originalLanguageId côté Toad
        if (isset($validatedData['languageId'])) {
            $validatedData['originalLanguageId'] = $validatedData['languageId'];
            unset($validatedData['languageId']);
        }

        $newFilm = $this->filmService->createFilm($validatedData);

        if ($newFilm) {
            return redirect()
                ->route('films.show', $newFilm['filmId'] ?? $newFilm['id'])
                ->with('success', 'Film créé avec succès !');
        }

        return redirect()->back()->withInput()->with('error', 'Erreur lors de la création du film. Veuillez réessayer.');
    }

    public function edit($id)
    {
        $film = $this->filmService->getFilmById($id);

        if (!$film) {
            abort(404, 'Film non trouvé');
        }

        return view('films.edit', [
            'film'            => $film,
            'languages'       => $this->getLanguages(),
            'specialFeatures' => $this->getSpecialFeatures(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string|max:1000',
            'releaseYear'     => 'required|integer|min:1900|max:2100',
            'languageId'      => 'required|integer|min:1',
            'length'          => 'nullable|integer|min:1|max:500',
            'rating'          => 'nullable|string|in:G,PG,PG-13,R,NC-17',
            'specialFeatures' => 'nullable|array',
            'specialFeatures.*' => 'string',
            'rentalDuration'  => 'required|integer|min:1|max:30',
            'rentalRate'      => 'required|numeric|min:0',
            'replacementCost' => 'required|numeric|min:0',
        ], [
            'title.required'          => 'Le titre est obligatoire.',
            'title.max'               => 'Le titre ne peut pas dépasser 255 caractères.',
            'releaseYear.required'    => 'L\'année de sortie est obligatoire.',
            'releaseYear.min'         => 'L\'année doit être supérieure ou égale à 1900.',
            'releaseYear.max'         => 'L\'année ne peut pas dépasser 2100.',
            'languageId.required'     => 'La langue est obligatoire.',
            'rating.in'               => 'La note doit être G, PG, PG-13, R ou NC-17.',
            'rentalDuration.required' => 'La durée de location est obligatoire.',
            'rentalRate.required'     => 'Le tarif de location est obligatoire.',
            'replacementCost.required'=> 'Le coût de remplacement est obligatoire.',
        ]);

        if (!empty($validatedData['specialFeatures'])) {
            $validatedData['specialFeatures'] = implode(',', $validatedData['specialFeatures']);
        }

        if (isset($validatedData['languageId'])) {
            $validatedData['originalLanguageId'] = $validatedData['languageId'];
            unset($validatedData['languageId']);
        }

        $updatedFilm = $this->filmService->updateFilm($id, $validatedData);

        if ($updatedFilm) {
            return redirect()
                ->route('films.show', $updatedFilm['filmId'] ?? $updatedFilm['id'])
                ->with('success', 'Film mis à jour avec succès !');
        }

        return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour du film. Veuillez réessayer.');
    }

    public function destroy($id)
    {
        $success = $this->filmService->deleteFilm($id);

        if ($success) {
            return redirect()->route('films.index')->with('success', 'Film supprimé avec succès !');
        }

        return redirect()->back()->with('error', 'Erreur lors de la suppression du film. Veuillez réessayer.');
    }

    private function getLanguages(): array
    {
        return [
            1 => 'Anglais', 2 => 'Français', 3 => 'Espagnol',
            4 => 'Allemand', 5 => 'Italien', 6 => 'Japonais',
            7 => 'Mandarin', 8 => 'Portugais',
        ];
    }

    private function getSpecialFeatures(): array
    {
        return [
            'Trailers'       => 'Bandes-annonces',
            'Commentaries'   => 'Commentaires',
            'Deleted Scenes' => 'Scènes supprimées',
            'Behind the Scenes' => 'Coulisses',
        ];
    }
}
