<?php

/**
 * @OA\Schema(
 *      schema="ListOfMismatches",
 *      @OA\Property(
 *          property="items",
 *          type="array",
 *          @OA\Items(
 *              ref="#/components/schemas/Mismatch"
 *          )
 *      )
 * )
 */
