<?php

/**
 * @OA\Schema(
 *      schema="UnexpectedError",
 *      title="UnexpectedError",
 *      description="An unexpected error has occurred",
 *      required={"code", "message"},
 *      @OA\MediaType(
 *          mediaType="application/json"
 *      ),
 *      @OA\Property(
 *          property="code",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="message",
 *          type="string"
 *      )
 * )
 */
