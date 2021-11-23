<?php

/**
 * @OA\Schema(
 *      schema="FailedImportMeta",
 *      allOf={
 *          @OA\Schema(ref="#/components/schemas/ImportMeta"),
 *          @OA\Property(
 *              property="error",
 *              type="string"
 *          )
 *      }
 * )
 */
