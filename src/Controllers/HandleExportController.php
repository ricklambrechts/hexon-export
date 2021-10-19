<?php

namespace RoyScheepens\HexonExport\Controllers;

use Exception;
use RoyScheepens\HexonExport\Contracts\PermalinkGenerator;
use RoyScheepens\HexonExport\Facades\HexonExport;

use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use SimpleXMLElement;
use Spatie\ArrayToXml\ArrayToXml;

class HandleExportController extends Controller
{
    /**
     * The request object
     * @var Request
     */
    protected Request $request;

    protected PermalinkGenerator $permalinkGenerator;

    /**
     * Class Constructor
     * @param Request $request
     */
    public function __construct(Request $request, PermalinkGenerator $permalinkGenerator)
    {
        $this->request = $request;
        $this->permalinkGenerator = $permalinkGenerator;
    }

    /**
     * Collects the data, converts it into XML and feeds it in the Export class
     * @return String A '1' if all went well, or a 422 with reasons why if not
     * @throws Exception
     */
    public function handle(): string
    {
        if (app()->has('debugbar')) {
            app('debugbar')->disable();
        }

        $input = $this->request->getContent();

        try {
            $xml = new SimpleXmlElement((string) $input);

            $result = HexonExport::handle($xml);

            if ($result->hasErrors()) {
                $error = implode('\n', $result->getErrors());

                Log::error($error);

                $response = response()->make((new ArrayToXml(
                    [
                        'voertuignr_hexon' => $result->getResourceId(),
                        'klantnummer' => $result->getCustomerNumber(),
                        'result' => 'FOUT',
                        'foutmelding' => $error,
                        'deeplink' => '',
                    ],
                    [
                        'rootElementName' => 'feedback',
                    ],
                    true,
                    'UTF-8'
                ))->prettify()->toXml());
                $response->setStatusCode(422);
                $response->withHeaders(['Content-Type' => 'application/xml']);
                return $response;
            }

            $resource = $result->getResource();
            if ($resource !== null) {
                $resource->refresh();
            }

            $response = response()->make((new ArrayToXml(
                [
                    'voertuignr_hexon' => $result->getResourceId(),
                    'klantnummer' => $result->getCustomerNumber(),
                    'result' => 'OK', //FOUT
                    'foutmelding' => collect($result->getErrors())->implode(', '),
                    'deeplink' => $resource ? $this->permalinkGenerator->generate($resource) : '',
                ],
                [
                    'rootElementName' => 'feedback',
                ],
                true,
                'UTF-8'
            ))->prettify()->toXml());

            $response->header('Content-Type', 'application/xml');
            return $response;
        } catch (Exception $e) {
            $error = 'Failed to parse XML due to malformed data.';

            Log::error($error);

            abort(422, $error);
        }

        return "1";
    }
}
