<?php

/**
 * @OA\Schema(
 *      schema="ClientError",
 *      title="ClientError",
 *      description="Bad request",
 *      required={"code", "message"},
 *      @OA\MediaType(
 *          mediaType="application/json"
 *      ),
 *      @OA\Property(
 *          property="message",
 *          type="string"
 *      )
 * )
 */
