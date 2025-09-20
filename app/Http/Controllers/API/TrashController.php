<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrashClearRequest;
use App\Http\Requests\TrashRestoreRequest;
use App\Models\Api\ApiLink;
use App\Models\Api\ApiLinkList;
use App\Models\Api\ApiNote;
use App\Models\Api\ApiTag;
use App\Repositories\TrashRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function getLinks(Request $request): JsonResponse
    {
        $links = ApiLink::onlyTrashed()
            ->byUser($request->user()->id)
            ->get();

        return response()->json($links->toArray());
    }

    public function getLists(Request $request): JsonResponse
    {
        $lists = ApiLinkList::onlyTrashed()
            ->byUser($request->user()->id)
            ->get();

        return response()->json($lists);
    }

    public function getTags(Request $request): JsonResponse
    {
        $tags = ApiTag::onlyTrashed()
            ->byUser($request->user()->id)
            ->get();

        return response()->json($tags);
    }

    public function getNotes(Request $request): JsonResponse
    {
        $notes = ApiNote::onlyTrashed()
            ->byUser($request->user()->id)
            ->get();

        return response()->json($notes);
    }

    public function clear(TrashClearRequest $request): JsonResponse
    {
        TrashRepository::delete($request->input('model'));

        return response()->json();
    }

    /**
     * Restore an entry from the trash.
     *
     * @param TrashRestoreRequest $request
     * @return JsonResponse
     */
    public function restore(TrashRestoreRequest $request): JsonResponse
    {
        TrashRepository::restore($request->input('model'), $request->input('id'));

        return response()->json();
    }
}
