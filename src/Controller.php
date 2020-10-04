<?php

namespace trostinsa\minesweeper\Controller;

use function trostinsa\minesweeper\View\runGame;
use function trostinsa\minesweeper\Model\createPlayingField;
use function trostinsa\minesweeper\Model\createCellsArray;
use function trostinsa\minesweeper\Model\Bombs;
use function trostinsa\minesweeper\Model\OpenArea;
use function trostinsa\minesweeper\Model\KitFlag;

function gameTime()
{
    global $cellsArrayPlayingField, $LoseGame, $CountCleanCells;
    $numberRoute = 1;
    while (true) {
        runGame($numberRoute);
        $numberRoute++;

        $inputString = \cli\prompt(
            "Введите координаты ячейки (x,y) через "
            . "запятую, без пробела.\nДля установки флага "
            . "в ячейку введите F после ввода координат"
        );
        $inputArray = explode(',', $inputString);
        if (
            !isset($inputArray[0]) || !isset($inputArray[1])
            || preg_match('/^[0-8]{1}$/', $inputArray[0]) == 0
            || preg_match('/^[0-8]{1}$/', $inputArray[1]) == 0
        ) {
            \cli\line("Неверные данные! Повторите попытку");
            $numberRoute--;
        } else {
            if (
                isset($inputArray[2])
                && ($inputArray[2] == 'F' || $inputArray[2] == 'f')
            ) {
                KitFlag($inputArray[0], $inputArray[1]);
            } else {
                if (Bombs($inputArray[0], $inputArray[1])) {
                    runGame($numberRoute);
                    \cli\line("Конец игры");
                    break;
                } else {
                    OpenArea($inputArray[0], $inputArray[1]);
                    if ($CountCleanCells == count($cellsArrayPlayingField) * count($cellsArrayPlayingField[0])) {
                        runGame($numberRoute);
                        \cli\line("Поздравляем! Вы выиграли!");
                        break;
                    }
                }
            }
        }
    }
}

function startGame()
{
    createPlayingField();
    createCellsArray();
    gameTime();
}