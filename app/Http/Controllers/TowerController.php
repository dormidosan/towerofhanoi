<?php

namespace App\Http\Controllers;

use App\CustomClasses\CustomLinkedList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class TowerController extends Controller
{
    const NUMBER_OF_DISKS = 7;
    /**
     * @return void
     */
    private function initGame()
    {
        $disks = range(self::NUMBER_OF_DISKS, 1);
        if (!session()->has('pegs')) {
            // Store the LinkedList for the pegs for the session
            $pegs = [
                new CustomLinkedList($disks),  // Peg 1 has all disks
                new CustomLinkedList([]),      // Peg 2 (empty)
                new CustomLinkedList([])       // Peg 3 (empty)
            ];
            session()->put('pegs', $pegs);
        }
    }

    /**
     * @return JsonResponse
     */
    public function cleanSession()
    {
        // Check if the session has the 'pegs' key
        if (session()->has('pegs')) {
            // Remove the 'pegs' key from the session
            session()->forget('pegs');
            return response()->json(['message' => 'Session data cleaned.']);
        }
        return response()->json(['message' => 'No session data to clean.']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function state(Request $request)
    {
        $this->initGame();

        // Retrieve the pegs from the session
        $pegs = session()->get('pegs');

        $pegsAsArrays = array_map(fn($peg) => $peg->toArray(), $pegs);
        // Check if the game is completed (all disks on the third peg)
        $isGameComplete = $pegs[2]->count() === self::NUMBER_OF_DISKS;

        // Return the serialized state of the game
        return response()->json([
            'pegs' => $pegsAsArrays,
            'isGameComplete' => $isGameComplete,
        ]);
    }

    /**
     * @param Request $request
     * @param int $from
     * @param int $to
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function move(Request $request, int $from, int $to)
    {
        $this->initGame();

        // Retrieve the pegs from the session
        $pegs = session()->get('pegs');

        // Convert from and to into integers (1-based to 0-based index)
        $fromIndex = $from - 1;
        $toIndex = $to - 1;

        // Validate the move
        if ($fromIndex < 0 || $fromIndex > 2 || $toIndex < 0 || $toIndex > 2) {
            return $this->jsonErrorResponse('Invalid peg index.',422  );
        }
        if (empty($pegs[$fromIndex])) {
            return $this->jsonErrorResponse("No disk to move from peg $from.");
        }

        $diskToMove = $pegs[$fromIndex]->peek(); // Get the top disk from the "from" peg
        $topDiskOnToPeg = $pegs[$toIndex]->count()?$pegs[$toIndex]->peek():false; // Get the top disk on the "to" peg (if any)

        //dd($diskToMove, $topDiskOnToPeg);
        // Check if the move is valid based on the rules of the game
        // If empty "TO" peg, any disk can be placed, otherwise, the disk to be placed
        // should be smaller than the top disk on the "TO" peg
        if (!empty($topDiskOnToPeg) && $diskToMove > $topDiskOnToPeg) {
            return $this->jsonErrorResponse(
                "Invalid move. Cannot place disk $diskToMove
                on top of disk $topDiskOnToPeg."
            );
        }

        // Move the disk
        $pegs[$fromIndex]->popBack(); // Remove the disk from the "from" peg
        $pegs[$toIndex]->pushBack($diskToMove); // Add the disk to the "to" peg

        // Save the updated state in the session
        session()->put('pegs', $pegs);

        // Check if the game is now complete
        $isGameComplete = $pegs[2]->count() === self::NUMBER_OF_DISKS;
        $pegsAsArrays = array_map(fn($peg) => $peg->toArray(), $pegs);

        return response()->json([
            'message' => "Moved disk in $diskToMove from peg $from to peg $to.",
            'pegs' => $pegsAsArrays,
            'isGameComplete' => $isGameComplete,
        ]);
    }

    /**
     * @param string $errorMessage
     * @param int $statusCode
     * @return JsonResponse
     */
    private function jsonErrorResponse(string $errorMessage, int $statusCode = 400)
    {
        return response()->json(['error' => $errorMessage], $statusCode);
    }

}
