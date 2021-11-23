<?php

/**
 * @OA\Schema(
 *      schema="ValidationErrors",
 *      title="Validation Errors",
 *      description="Invalid data in request body",
 *      @OA\MediaType(
 *          mediaType="application/json"
 *      ),
 *      @OA\Property(
 *           property="message",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="errors",
 *          type="object",
 *          example={
 *              "attribute_name"={"string"}
 *          },
 *          additionalProperties={
 *              @OA\Property(
 *                  property="items",
 *                  type="array",
 *                  @OA\Items(type="string")
 *              )
 *          }
 *      )
 * )
 */
