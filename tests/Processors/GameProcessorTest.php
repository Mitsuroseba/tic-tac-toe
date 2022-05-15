<?php

namespace Tests\Processors;

use App\Entity\GameTurn;
use App\Enum\Term;
use App\Exceptions\GameFinishedException;
use App\Exceptions\OccupiedSquareException;
use App\Exceptions\WrongTurnException;
use App\Models\GameResource;
use App\Processors\GameProcessor;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class GameProcessorTest extends TestCase
{
    protected GameProcessor $gameProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gameProcessor = new GameProcessor();
    }

    public function testSetPiece()
    {
        $game = new GameResource;
        $game->uuid = Str::uuid();
        $this->gameProcessor->reset($game, withScores: true);

        $gameTurn = new GameTurn(
            x: 0,
            y: 0,
            piece: Term::o,
        );

        // GameFinishedException.
        $game->victory = Term::o;
        try {
            $this->gameProcessor->setPiece($game, $gameTurn);
        }
        catch (\Exception $e) {
            $this->assertEquals(GameFinishedException::class, get_class($e));
        }
        $game->victory = null;

        // WrongTurnException.
        try {
            $this->gameProcessor->setPiece($game, $gameTurn);
        }
        catch (\Exception $e) {
            // Should start with x.
            $this->assertEquals(WrongTurnException::class, get_class($e));
        }
        $gameTurn->piece = Term::x;

        // Check normal turn results.
        $this->gameProcessor->setPiece($game, $gameTurn);
        $this->assertEquals(Term::x, $game->board[0][0]);
        $this->assertEquals(Term::o, $game->current_turn);
        $this->assertEquals(0, $game->score_x);
        $this->assertEquals(0, $game->score_y);
        $this->assertNull($game->victory);

        // OccupiedSquareException.
        try {
            $gameTurn->piece = Term::o;
            $this->gameProcessor->setPiece($game, $gameTurn);
        }
        catch (\Exception $e) {
            $this->assertEquals(OccupiedSquareException::class, get_class($e));
        }

        // Make vertical win situation on last line for x.
        $this->gameProcessor->reset($game, withScores: true);
        $game->board = [
            [Term::o, Term::o,    null],
            [Term::o, Term::x, Term::x],
            [null,    Term::o, Term::x],
        ];
        $gameTurn->piece = Term::x;
        $gameTurn->x = 2;
        $gameTurn->y = 0;

        $this->gameProcessor->setPiece($game, $gameTurn);
        $this->assertEquals(Term::x, $game->board[$gameTurn->y][$gameTurn->x]);
        $this->assertEquals(Term::x, $game->victory);
        $this->assertEquals(1, $game->score_x);

        // Make diagonal win situation on main line for o.
        $this->gameProcessor->reset($game, withScores: true);
        $game->current_turn = Term::o;
        $game->board = [
            [Term::o, Term::x,    null],
            [Term::x, Term::o, Term::x],
            [Term::x, Term::o,    null],
        ];
        $gameTurn->piece = Term::o;
        $gameTurn->x = 2;
        $gameTurn->y = 2;

        $this->gameProcessor->setPiece($game, $gameTurn);
        $this->assertEquals(Term::o, $game->board[$gameTurn->y][$gameTurn->x]);
        $this->assertEquals(Term::o, $game->victory);
        $this->assertEquals(0, $game->score_x);
        $this->assertEquals(1, $game->score_y);

        // Make normal tie.
        $this->gameProcessor->reset($game, withScores: true);
        $game->current_turn = Term::x;
        $game->board = [
            [Term::x, Term::o, Term::x],
            [null,    Term::o, Term::o],
            [Term::o, Term::x, Term::x],
        ];
        $gameTurn->piece = Term::x;
        $gameTurn->x = 0;
        $gameTurn->y = 1;

        $this->gameProcessor->setPiece($game, $gameTurn);
        $this->assertEquals(Term::x, $game->board[$gameTurn->y][$gameTurn->x]);
        $this->assertEquals(Term::tie, $game->victory);
        $this->assertEquals(0, $game->score_x);
        $this->assertEquals(0, $game->score_y);

        // Make abnormal tie with existing null on the field.
        $this->gameProcessor->reset($game, withScores: true);
        $game->current_turn = Term::o;
        $game->board = [
            [Term::x,    null, Term::o],
            [Term::o, Term::x, Term::x],
            [Term::x,    null, Term::o],
        ];
        $gameTurn->piece = Term::o;
        $gameTurn->x = 1;
        $gameTurn->y = 2;

        $this->gameProcessor->setPiece($game, $gameTurn);
        $this->assertEquals(Term::o, $game->board[$gameTurn->y][$gameTurn->x]);
        $this->assertEquals(Term::tie, $game->victory);
        $this->assertEquals(0, $game->score_x);
        $this->assertEquals(0, $game->score_y);
    }
}
