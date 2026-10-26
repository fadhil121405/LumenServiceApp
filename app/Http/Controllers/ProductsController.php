<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use SimpleXMLElement;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        if ($error = $this->checkAccept($request)) {
            return $error;
        }

        $products = Product::all()->toArray();

        return $this->respond($request, $products, 200, 'products');
    }

    public function store(Request $request)
    {
        if ($error = $this->checkAccept($request)) {
            return $error;
        }
        if ($error = $this->checkContentType($request)) {
            return $error;
        }

        $validator = $this->getValidationFactory()->make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->respond($request, [
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422, 'error');
        }

        $product = Product::create($request->only($this->fields()));

        return $this->respond($request, $product->toArray(), 201, 'product');
    }

    public function show(Request $request, $productId)
    {
        if ($error = $this->checkAccept($request)) {
            return $error;
        }

        $product = Product::find($productId);
        if (!$product) {
            return $this->respond($request, ['message' => 'Product not found'], 404, 'error');
        }

        return $this->respond($request, $product->toArray(), 200, 'product');
    }

    public function update(Request $request, $productId)
    {
        if ($error = $this->checkAccept($request)) {
            return $error;
        }
        if ($error = $this->checkContentType($request)) {
            return $error;
        }

        $product = Product::find($productId);
        if (!$product) {
            return $this->respond($request, ['message' => 'Product not found'], 404, 'error');
        }

        $validator = $this->getValidationFactory()->make($request->all(), $this->rules($productId));
        if ($validator->fails()) {
            return $this->respond($request, [
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422, 'error');
        }

        $product->update($request->only($this->fields()));

        return $this->respond($request, $product->toArray(), 200, 'product');
    }

    public function destroy(Request $request, $productId)
    {
        if ($error = $this->checkAccept($request)) {
            return $error;
        }

        $product = Product::find($productId);
        if (!$product) {
            return $this->respond($request, ['message' => 'Product not found'], 404, 'error');
        }

        $product->delete();

        return $this->respond($request, ['message' => 'Product deleted'], 200, 'result');
    }


    private function fields()
    {
        return ['name', 'sku', 'description', 'price', 'stock', 'category', 'status'];
    }

    private function rules($productId = null)
    {
        $skuRule = 'required|string|max:50|unique:products,sku';
        if ($productId) {
            $skuRule .= ',' . $productId;
        }

        return [
            'name' => 'required|string|max:255',
            'sku' => $skuRule,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'required|string|max:100',
            'status' => 'required|in:active,inactive',
        ];
    }

    private function checkAccept(Request $request)
    {
        $accept = $request->header('Accept');

        if ($accept !== 'application/json' && $accept !== 'application/xml') {
            return response('Not Acceptable!', 406);
        }

        return null;
    }

    private function checkContentType(Request $request)
    {
        $contentType = strtolower(trim(explode(';', (string) $request->header('Content-Type'))[0]));

        if ($contentType !== 'application/json') {
            return response('Unsupported Media Type', 415);
        }

        return null;
    }

    private function respond(Request $request, array $data, $status, $rootName)
    {
        if ($request->header('Accept') === 'application/xml') {
            $xml = new SimpleXMLElement('<' . $rootName . '/>');
            $this->arrayToXml($data, $xml);

            return response($xml->asXML(), $status)
                ->header('Content-Type', 'application/xml');
        }

        return response()->json($data, $status);
    }

    private function arrayToXml(array $data, SimpleXMLElement $xml)
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item';
            }

            if (is_array($value)) {
                $child = $xml->addChild($key);
                $this->arrayToXml($value, $child);
            } else {
                $xml->addChild($key, htmlspecialchars((string) $value));
            }
        }
    }
}