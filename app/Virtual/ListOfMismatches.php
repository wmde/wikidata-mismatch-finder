<?php

namespace App\Http\Api\Controllers;

/**
 * @OA\Schema(
 *     title="List of Mismatches",
 *     description="List of Mismatches",
 *     @OA\Xml(
 *         name="ListOfMismatchess"
 *     )
 * )
 */

class ListOfMismatches
{
    /**
    * @OA\Property(
    *     title="List of Mismatches"
    * )
    *
    * @var \App\Virtual\Models\Mismatch[]
    */
    private $data;
}
