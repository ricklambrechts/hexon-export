<?php

namespace RoyScheepens\HexonExport\Controllers;

use Exception;
use RoyScheepens\HexonExport\Facades\HexonExport;

use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use SimpleXmlElement;

class HandleExportController extends Controller
{
    /**
     * The request object
     * @var Request
     */
    protected Request $request;

    /**
     * Class Constructor
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Collects the data, converts it into XML and feeds it in the Export class
     * @return String A '1' if all went well, or a 422 with reasons why if not
     * @throws Exception
     */
    public function handle(): string
    {
        $input = $this->request->getContent();

        try {
            $xml = new SimpleXmlElement($input);

            $result = HexonExport::handle($xml);

            if ($result->hasErrors()) {
                $error = implode('\n', $result->getErrors());

                Log::error($error);

                abort(422, $error);
                exit;
            }
        } catch (Exception $e) {
            $error = 'Failed to parse XML due to malformed data.';

            Log::error($error);

            abort(422, $error);
        }

        // Hexon requires a response of '1' if all went well.
        exit('1');
    }
}
