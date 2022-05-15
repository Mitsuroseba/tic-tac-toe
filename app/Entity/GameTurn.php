<?php

namespace App\Entity;

use App\Enum\Term;

class GameTurn {
    public function __construct(
        public int $x = 0,
        public int $y = 0,
        public Term $piece = Term::x,
    ) {}
}
