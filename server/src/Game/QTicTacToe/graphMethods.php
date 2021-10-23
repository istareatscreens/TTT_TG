<?php

namespace Game\Game\QTicTacToe;

function dFSCycleCheck($adjacencyList)
{
    $visited = array_fill(0, count($adjacencyList), false);
    $dfs = function (int $quadrant, int $parent = -1) use (
        &$visited,
        &$dfs,
        &$adjacencyList
    ): bool {
        if ($visited[$quadrant]) {
            return false;
        }

        $visited[$quadrant] = true;
        $neighbours = $adjacencyList[$quadrant];
        foreach ($neighbours as $node) {
            return ($dfs($node)) ? true : $quadrant !== $parent;
        }
    };

    return $dfs;
}
