<?php

/**
 * @OA\Schema(
 *      schema="ImportMeta",
 *      @OA\Property(
 *          property="id",
 *          type="number"
 *      ),
 *      @OA\Property(
 *          property="status",
 *          type="string",
 *          enum={"pending","failed","completed"}
 *      ),
 *      @OA\Property(
 *          property="description",
 *          type="string",
 *          maxLength=350
 *      ),
 *
 *      @OA\Property(
 *          property="expires",
 *          type="string",
 *          format="date"
 *      ),
 *      @OA\Property(
 *          property="created",
 *          type="string",
 *          format="date"
 *      ),
 *      @OA\Property(
 *          property="uploader",
 *          ref="#/components/schemas/User"
 *      )
 * )
 */
