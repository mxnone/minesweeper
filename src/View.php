<?php

namespace trostinsa\minesweeper\View;

function runGame($numberRoute)
{
    global $cellsArrayPlayingField , $bombArray;
    \cli\line(sprintf('%20s', "Ход №" . $numberRoute));
    $line = sprintf('%2s', ' ');
    for ($i = 0; $i < MAX_X; $i++) {
        $line .= sprintf('%2s', $i);
    }
    \cli\line($line);
    $line = '';

    for ($i = 0; $i < MAX_Y; $i++) {
        $line .= sprintf('%2s', $i);
        for ($j = 0; $j < MAX_X; $j++) {
            if ($cellsArrayPlayingField[$i][$j]['clean'] == true) {
                if ($cellsArrayPlayingField[$i][$j]['registered'] == true) {
                    $line .= sprintf('%2s', 'F');
                } else {
                    if ($cellsArrayPlayingField[$i][$j]['bomb'] == true) {
                        $line .= sprintf('%2s', '*');
                    } else {
                        if ($cellsArrayPlayingField[$i][$j]['hereabout'] == 0) {
                            $line .= sprintf('%2s', '-');
                        } else {
                            $line .= sprintf(
                                '%2s',
                                $cellsArrayPlayingField[$i][$j]['hereabout']
                            );
                        }
                    }
                }
            } else {
                $line .= sprintf('%2s', '.');
            }
        }
        \cli\line($line);
        $line = '';
    }
}