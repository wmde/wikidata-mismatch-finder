<?php

/**
 * @OA\Schema(
 *      schema="Mismatch",
 *      allOf={
 *          @OA\Schema(type="object", properties={
 *              @OA\Property(
 *                  property="id",
 *                 type="string"
 *              ),
 *              @OA\Property(
 *                  property="item_id",
 *                  type="string"
 *              ),
 *              @OA\Property(
 *                  property="statement_guid",
 *                  type="string"
 *              ),
 *              @OA\Property(
 *                 property="property_id",
 *                 type="string"
 *              ),
 *              @OA\Property(
 *                 property="wikidata_value",
 *                 type="string"
 *              ),
 *              @OA\Property(
 *                  property="external_value",
 *                  type="string"
 *              ),
 *              @OA\Property(
 *                  property="external_url",
 *                  type="string"
 *              ),
 *              @OA\Property(
 *                  property="import",
 *                  type="object",
 *                  ref="#/components/schemas/ImportMeta"
 *              ),
 *              @OA\Property(
 *                  property="updated_at",
 *                  type="string",
 *                  format="date-time"
 *              ),
 *              @OA\Property(
 *                  property="reviewer",
 *                  type="object",
 *                  ref="#/components/schemas/User"
 *              )
 *      }),
 *      @OA\Schema(ref="#/components/schemas/FillableMismatch")
 *      }
 * )
 */

 // TODO: check import and reviewer properties,
 // property type and ref are not rendering at the same time
