<?php

namespace Game\Game\QTicTacToe;

class GraphMethods
{

    public static function checkForParallelCycle($adjacencyGraph, $quadrant)
    {
        $map = [];
        foreach ($adjacencyGraph[$quadrant] as $node) {
            if (!key_exists($node, $map)) {
                $map[$node] = 1;
            } else if (++$map[$node] === 2) {
                return true;
            }
        }
    }

    public static function dFSCycleCheck($adjacencyList)
    {
        $visited = array_fill(0, count($adjacencyList), false);
        $dfs = function (int $quadrant, int $parent = -1) use (
            &$visited,
            &$dfs,
            &$adjacencyList
        ): bool {
            $visited[$quadrant] = true;
            foreach ($adjacencyList[$quadrant] as $node) {
                if (!$visited[$node]) {
                    if ($dfs($node, $quadrant)) return true;
                } else if ($node !== $parent && $parent !== -1) {
                    return true;
                }
            }
            return false;
        };

        return $dfs;
    }
}
