<?php

namespace App\Processors;

use App\Entity\GameTurn;
use App\Enum\Term;
use App\Exceptions\GameFinishedException;
use App\Exceptions\OccupiedSquareException;
use App\Exceptions\WrongTurnException;
use App\Models\GameResource;
use Illuminate\Http\Request;

class GameProcessor
{
    /**
     * Reset game resource
     * @param GameResource $gameResource
     * @param bool $withScores
     */
    public function reset(GameResource $game, bool $withScores = false)
    {
        $game->board = [
            [null, null, null,],
            [null, null, null,],
            [null, null, null,],
        ];

        if ($withScores) {
            $game->score_x = 0;
            $game->score_y = 0;
        }

        $game->current_turn = Term::x;
        $game->victory = null;
    }

    /**
     * Set a piece.
     *
     * @throws OccupiedSquareException
     * @throws GameFinishedException
     * @throws WrongTurnException
     */
    public function setPiece(GameResource $gameResource, GameTurn $gameTurn)
    {
        if ($gameResource->victory !== null) {
            throw new GameFinishedException();
        }

        if ($gameResource->current_turn !== $gameTurn->piece) {
            throw new WrongTurnException();
        }

        if ($gameResource->board[$gameTurn->y][$gameTurn->x] !== null) {
            throw new OccupiedSquareException();
        }

        $temp = $gameResource->board;
        $temp[$gameTurn->y][$gameTurn->x] = $gameTurn->piece;
        $gameResource->board = $temp;
        $check = $this->checkVictory($gameResource->board);

        if ($check === Term::o) {
            $gameResource->victory = Term::o;
            $gameResource->score_y++;
        }
        elseif ($check === Term::x) {
            $gameResource->victory = Term::x;
            $gameResource->score_x++;
        }
        elseif ($check === Term::tie) {
            $gameResource->victory = Term::tie;
        }

        $gameResource->current_turn = $gameResource->current_turn === Term::x ? Term::o : Term::x;

    }

    /**
     * Checks victory: return result or null if not decided yet.
     *
     * @param array $board
     * @return Term|null
     */
    private function checkVictory(array $board) :Term|null
    {
        // Horizontal.
        for ($i = 0; $i < 3; $i++) {
            $result[] = $this->checkLine($board[$i]);
        }

        // Verticals.
        for ($i = 0; $i < 3; $i++) {
            $result[] = $this->checkLine([$board[0][$i], $board[1][$i], $board[2][$i]]);
        }

        // Diagonals.
        $result[] = $this->checkLine([$board[0][0], $board[1][1], $board[2][2]]);
        $result[] = $this->checkLine([$board[0][2], $board[1][1], $board[2][0]]);

        $iMax = count($result);
        $sumNull = 0;

        for ($i = 0; $i < $iMax; $i++) {
            if ($result[$i] === Term::x) {
                return Term::x;
            }
            if ($result[$i] === Term::o) {
                return Term::o;
            }
            if ($result[$i] === null) {
                $sumNull++;
            }
        }

        if ($sumNull > 0) {
            return null;
        }

        return Term::tie;
    }

    /**
     * Check one line for victory: return result or null if not decided yet.
     *
     * @param array $line
     * @return Term|null
     */
    private function checkLine(array $line) :Term|null
    {
        $iMax = count($line);
        $sumX = $sumY = 0;

        for ($i = 0; $i < $iMax; $i++) {
            switch ($line[$i]) {
                case Term::x:
                    $sumX++;
                break;
                case Term::o:
                    $sumY++;
                break;
            }
        }

        if ($sumX === $iMax) {
            return Term::x;
        }

        if ($sumY === $iMax) {
            return Term::o;
        }

        if ($sumX !== 0 && $sumY !== 0) {
            return Term::tie;
        }

        return null;
    }
}
