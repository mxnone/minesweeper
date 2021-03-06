<?php

namespace trostinsa\minesweeper\Controller;

use function trostinsa\minesweeper\View\showGame;
use function trostinsa\minesweeper\View\InformationOfGames;
use function trostinsa\minesweeper\View\showTurnInfo;

use function trostinsa\minesweeper\Model\makeVars;
use function trostinsa\minesweeper\Model\createMakeArr;
use function trostinsa\minesweeper\Model\itsBombs;
use function trostinsa\minesweeper\Model\openArea;
use function trostinsa\minesweeper\Model\setFlag;
use function trostinsa\minesweeper\Model\insertInfo;
use function trostinsa\minesweeper\Model\postGameId;
use function trostinsa\minesweeper\Model\insertTurnInfo;
use function trostinsa\minesweeper\Model\getVars;

function isCorrect($x, $y)
{
    if (is_numeric($x) && is_numeric($y)) {
        $temp = METERING - 1;
        if ($x >= 0 && $x <= $temp && $y >= 0 && $y <= $temp) {
            return true;
        }
    }
    return false;
}

function updateDatabase($gameResult)
{
    $gameDatabase = new \SQLite3('gamedb.db');
    $gameId = GAME_ID;

    $query = "UPDATE gamesInfo
        SET gameResult = '$gameResult'
        WHERE idGame = '$gameId'";
    $gameDatabase->exec($query);
}

function gameLoop()
{
    global $cellsArray, $openedCellsCount;
    $flag = "-";
    $turnCount = 1;
    while (true) {
        showGame($turnCount);

        $inputString = \cli\prompt(
            "Введите координаты x, y ячейки через "
            . "запятую без пробела, для установки "
            . "флага в ячейку введите "
            . "F или f после ввода координат (через запятую, "
            . "без пробела)"
        );

        $inputArray = explode(',', $inputString);

        $coordX = $inputArray[0];
        $coordY = $inputArray[1];

        if (!isCorrect($coordX, $coordY)) {
            \cli\line("Неверно введены данные! Попробуйте еще раз");
            continue;
        }

        if (isset($inputArray[2])) {
            $flag = $inputArray[2];
            setFlag($coordX, $coordY);
            insertTurnInfo($turnCount, "Установлен флаг", $coordX, $coordY);
            $turnCount++;
            continue;
        }

        if (itsBombs($coordX, $coordY)) {
            showGame($turnCount);
            \cli\line("GAME OVER");
            insertTurnInfo($turnCount, "Игра проиграна", $coordX, $coordY);
            updateDatabase("Игра проиграна");
            break;
        } else {
            openArea($coordX, $coordY);
            insertTurnInfo($turnCount, "Открыта область", $coordX, $coordY);
            if ($openedCellsCount == count($cellsArray) * count($cellsArray[0])) {
                showGame($turnCount);
                \cli\line("CONGRATULATIONS! YOU WON");
                insertTurnInfo($turnCount, "Игра выиграна", $coordX, $coordY);
                updateDatabase("Игра выиграна");
                break;
            }
        }
        $turnCount++;
    }
}

function newGame()
{
    makeVars();
    insertInfo();
    postGameId();
    createMakeArr("new", GAME_ID);
    gameLoop();
    exit();
}

function listGames()
{
    if (!file_exists("gamedb.db")) {
        \cli\line("База данных не обнаружена!");
        return;
    }
    $gameDatabase = new \SQLite3('gamedb.db');
    $result = $gameDatabase->query("SELECT * FROM gamesInfo");

    while ($row = $result->fetchArray()) {
        InformationOfGames($row);
        $query = "SELECT 
            gameTurn, 
            coordinates, 
            result 
            FROM concreteGame 
            WHERE idGame='$row[0]'
            ";
        $gameTurns = $gameDatabase->query($query);
        while ($gameTurnsRow = $gameTurns->fetchArray()) {
            showTurnInfo($gameTurnsRow);
        }
    }
}

function idExists($id)
{
    $gameDatabase = new \SQLite3('gamedb.db');
    $query = "SELECT EXISTS(SELECT 1 FROM gamesInfo WHERE idGame='$id')";
    $flag = $gameDatabase->querySingle($query);
    if ($flag == 0) {
        return false;
    } else {
        return true;
    }
}

function replayGame($id)
{
    if (!file_exists("gamedb.db")) {
        \cli\line("База данных не создана!");
        return;
    }

    if (!idExists($id)) {
        \cli\line("Выбранной игры не существует");
        return;
    } else {
        getVars($id);
        insertInfo();
        postGameId();
        createMakeArr("replay", $id);
        gameLoop();
        exit();
    }
}

function startGame()
{
    while (true) {
        $command = \cli\prompt(
            "Введите один из доступных ключей:\n"
            . "--new - новая игра\n"
            . "--list - вывод списка всех игр\n"
            . "--replay id - повтор игры с идентивикатором id\n"
            . "--exit - выход\n"
        );
        if ($command == "--new") {
            newGame();
        } elseif ($command == "--list") {
            listGames();
        } elseif (preg_match('/(^--replay [0-9]+$)/', $command) != 0) {
            $temp = explode(' ', $command);
            $id = $temp[1];
            unset($temp);
            replayGame($id);
        } elseif ($command == "--exit") {
            exit();
        } else {
            \cli\line("Неверный ключ! Выберите один из предложенных!");
        }
    }
}