<?php

namespace App\Http\Controllers;

use App\Enum\Term;
use App\Exceptions\GameFinishedException;
use App\Exceptions\OccupiedSquareException;
use App\Exceptions\WrongTurnException;
use App\Entity\GameTurn;
use App\Models\GameResource;
use App\Processors\GameProcessor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GameResourceController extends Controller
{
    /**
     * The game processor:
     * takes game object and input,
     * returns new game object or exception
     *
     * @var GameProcessor
     */
    protected GameProcessor $gameProcessor;

    /**
     * Create a new controller instance.
     *
     * @param  GameProcessor $gameProcessor
     * @return void
     */
    public function __construct(GameProcessor $gameProcessor)
    {
        $this->gameProcessor = $gameProcessor;
    }

    /**
     * Create game resource.
     *
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        $game = new GameResource;
        $game->uuid = Str::uuid();

        $this->gameProcessor->reset($game, withScores: true);

        $game->save();

        return response()->json($game);
    }

    /**
     * Get game object.
     *
     * @return JsonResponse
     */
    public function getGame(Request $request, string $id): JsonResponse
    {
        $game = GameResource::where('uuid', $id)->first();

        if ($game === null) {
            return response()->json([], status: 404);
        }

        return response()->json($game);

    }

    /**
     * Restart game.
     *
     * @return JsonResponse
     */
    public function restart(Request $request, string $id): JsonResponse
    {
        $game = GameResource::where('uuid', $id)->first();

        if ($game === null) {
            return response()->json([], status: 404);
        }

        $this->gameProcessor->reset($game);

        $game->save();

        return response()->json($game);
    }

    /**
     * Reset the game (and the board state).
     *
     * @return JsonResponse
     */
    public function delete(Request $request, string $id): JsonResponse
    {
        $game = GameResource::where('uuid', $id)->first();

        if ($game === null) {
            return response()->json([], status: 404);
        }

        $this->gameProcessor->reset($game, withScores: true);

        $game->save();

        return response()->json([], 204);
    }

    /**
     * Set a piece.
     *
     * @return JsonResponse
     */
    public function setPiece(Request $request, string $id, string $piece): JsonResponse
    {
        $validation = Validator::make($request->all(),[
            'x' => 'required|numeric|min:0|max:2',
            'y' => 'required|numeric|min:0|max:2',
        ]);

        if (
            $validation->fails()
            || !in_array($piece, [Term::x->name, Term::o->name], true)
        ) {
            // If a piece is placed out of bounds.
            return response()->json([], status: 422);
        }

        $game = GameResource::where('uuid', $id)->first();

        if ($game === null) {
            return response()->json([], status: 404);
        }

        $gameTurn = new GameTurn(
            x: $request->get('x'),
            y: $request->get('y'),
            piece: $piece === Term::x->name ? Term::x : Term::o
        );

        try {
            $this->castEnum($game);
            $this->gameProcessor->setPiece($game, $gameTurn);
            $game->save();
            return response()->json($game);
        }
        catch (\Exception $e) {
            $status = match (get_class($e)) {
                OccupiedSquareException::class => 409,
                WrongTurnException::class => 406,
                GameFinishedException::class => 425,
            };

            return response()->json([], status: $status);
        }
    }

    private function castEnum(GameResource $game) {

    }
}
