<?php namespace mxnone\minesweeper\Controller;
    use function mxnone\minesweeper\View\showGame;
    
    function startGame() {
        echo "Game started".PHP_EOL;
        showGame();
    }
?>