<?php

/**
 * @OA\Schema(
 *      schema="ListOfImportMeta",
 *      @OA\Property(
 *          property="items",
 *          type="array",
 *          @OA\Items(
 *              anyOf={
 *                  @OA\Schema(ref="#/components/schemas/ImportMeta"),
 *                  @OA\Schema(ref="#/components/schemas/FailedImportMeta")
 *              }
 *          )
 *      )
 * )
 */
