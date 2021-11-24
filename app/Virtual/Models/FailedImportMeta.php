<?php

/**
 * @OA\Schema(
 *      schema="FailedImportMeta",
 *      allOf={
 *          @OA\Schema(ref="#/components/schemas/ImportMeta"),
 *          @OA\Schema(
 *              type="object",
 *              properties={
 *                  @OA\Property(
 *                      property="error",
 *                      type="string"
 *                  )
 *              }
 *          )
 *     }
 * )
 */
