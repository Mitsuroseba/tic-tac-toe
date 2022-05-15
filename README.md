Tic-tac-toe API with Laravel 9.0 

Highlights:

1) app/Http/Controllers/GameResourceController.php
    1) Endpoints logic.
2) app/Processors/GameProcessor.php
    1) Main tic-tac-toe logic.
3) tests/Processors/GameProcessorTest.php:126
    1) PHP unit tests, plus test with abnormal tie, when some fields are empty.
4) database/migrations/2022_05_14_164203_create_game_resources_table.php
    1) DB migrations.
5) postman_collection.json
    1) Postman collection file.


Legend:
0 == Term for X
1 == Term for O
2 == Term for TIE

Example:
{
    "uuid": "dd7a77b5-1389-4f45-8c08-5ed32d1994dd",
    "board": [
        [
            1,
            1,
            1
        ],
        [
            0,
            0,
            null
        ],
        [
            0,
            1,
            0
        ]
    ],
    "score_x": 0,
    "score_y": 1,
    "current_turn": 0,
    "victory": 1
}
