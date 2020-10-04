<?php

namespace trostinsa\minesweeper\Model;

function createPlayingField()
{
    define("MAX_X", 9);
    define("MAX_Y", 9);
    define("BOMBS_COUNT", 10);

    $cellsArrayPlayingField = array();
    $bombArray = array();
    $CountCleanCells = 0;
}

function SwitchOn($array, $x, $y)
{
    if (isset($array)) {
        for ($i = 0; $i < count($array); $i++) {
            if ($array[$i]['x'] == $x && $array[$i]['y'] == $y) {
                return true;
            }
        }
    }
    return false;
}

function createbombArray($position)
{
    global $bombArray;
    for ($i = 0; $i < BOMBS_COUNT; $i++) {
        $randX = rand(0, MAX_X - 1);
        $randY = rand(0, MAX_Y - 1);
        if (!SwitchOn($bombArray, $randX, $randY)) {
            $bombArray[$i] = array('x' => $randX, 'y' => $randY);
        } else {
            createbombArray($i);
            break;
        }
    }
    if (count($bombArray) == BOMBS_COUNT) {
        return;
    }
}

function deployBombs()
{
    global $cellsArrayPlayingField, $bombArray;
    for ($i = 0; $i < BOMBS_COUNT; $i++) {
        $x = $bombArray[$i]['x'];
        $y = $bombArray[$i]['y'];
        $cellsArrayPlayingField[$y][$x]['bomb'] = true;
        for ($j = $x - 1; $j <= $x + 1; $j++) {
            for ($k = $y - 1; $k <= $y + 1; $k++) {
                if (isset($cellsArrayPlayingField[$j]) && isset($cellsArrayPlayingField[$k][$j])) {
                    $cellsArrayPlayingField[$k][$j]['hereabout'] += 1;
                }
            }
        }
    }
}

function createCellsArray()
{
    global $cellsArrayPlayingField;
    for ($i = 0; $i < MAX_Y; $i++) {
        for ($j = 0; $j < MAX_X; $j++) {
            $cellsArrayPlayingField[$i][$j] = array('clean' => false, 'registered' => false,
                                        'bomb' => false, 'hereabout' => 0);
        }
    }
    createbombArray(0);
    deployBombs();
}

function Bombs($x, $y)
{
    global $cellsArrayPlayingField, $LoseGame;
    if (
        $cellsArrayPlayingField[$y][$x]['bomb'] == true
        && $cellsArrayPlayingField[$y][$x]['registered'] == false
    ) {
        $cellsArrayPlayingField[$y][$x]['clean'] = true;
        return true;
    }
    return false;
}

function openNearbyCells($x, $y)
{
    global $cellsArrayPlayingField;
    if (
        isset($cellsArrayPlayingField[$y])
        && isset($cellsArrayPlayingField[$y][$x])
    ) {
        OpenArea($x, $y);
    }
}

function OpenArea($x, $y)
{
    global $CountCleanCells, $cellsArrayPlayingField;
    if (
        $cellsArrayPlayingField[$y][$x]['clean'] == false
        && $cellsArrayPlayingField[$y][$x]['registered'] == false
    ) {
        $cellsArrayPlayingField[$y][$x]['clean'] = true;
        $CountCleanCells += 1;
        if ($cellsArrayPlayingField[$y][$x]['hereabout'] != 0) {
            return;
        }
    } else {
        return;
    }
    for ($i = $x - 1; $i <= $x + 1; $i++) {
        for ($j = $y - 1; $j <= $y + 1; $j++) {
            openNearbyCells($i, $j);
        }
    }
}

function KitFlag($x, $y)
{
    global $CountCleanCells, $cellsArrayPlayingField;
    if ($cellsArrayPlayingField[$y][$x]['registered'] == false) {
        if ($cellsArrayPlayingField[$y][$x]['clean'] == false) {
            $cellsArrayPlayingField[$y][$x]['registered'] = true;
            $cellsArrayPlayingField[$y][$x]['clean'] = true;
            $CountCleanCells++;
        }
    } else {
            $cellsArrayPlayingField[$y][$x]['registered'] = false;
            $cellsArrayPlayingField[$y][$x]['clean'] = false;
            $CountCleanCells--;
    }
}