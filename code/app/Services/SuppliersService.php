<?php

namespace App\Services;

use App\Exceptions\AuthException;

use Auth;
use Log;
use DB;
use PDF;

use App\Formatters\Product as ProductFormatter;
use App\User;
use App\Supplier;
use App\Product;

class SuppliersService extends BaseService
{
    private function accessible($user)
    {
        $suppliers_id = [];

        foreach($user->targetsByAction('supplier.modify', false) as $supplier) {
            $suppliers_id[] = $supplier->id;
        }

        foreach($user->targetsByAction('supplier.orders', false) as $supplier) {
            $suppliers_id[] = $supplier->id;
        }

        foreach($user->targetsByAction('supplier.shippings', false) as $supplier) {
            $suppliers_id[] = $supplier->id;
        }

        return array_unique($suppliers_id);
    }

    public function list($term = '', $all = false)
    {
        $user = $this->ensureAuth();

        $query = Supplier::orderBy('name', 'asc');

        if (!empty($term)) {
            $query->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', "%$term%");
            });
        }

        if ($user->can('supplier.view', $user->gas) == false && $user->can('supplier.add', $user->gas) == false) {
            $suppliers_id = $this->accessible($user);
            $query->whereIn('id', $suppliers_id);
        }

        if ($all)
            $query->filterEnabled();

        $suppliers = $query->get();
        return $suppliers;
    }

    public function show($id)
    {
        $user = $this->ensureAuth();

        if ($user->can('supplier.view', $user->gas) == false && $user->can('supplier.add', $user->gas) == false) {
            $suppliers_id = $this->accessible($user);
            if (in_array($id, $suppliers_id) == false) {
                throw new AuthException(401);
            }
        }

        return Supplier::withTrashed()->findOrFail($id);
    }

    private function setCommonAttributes($supplier, $request)
    {
        $this->setIfSet($supplier, $request, 'name');
        $this->setIfSet($supplier, $request, 'business_name');
        $this->setIfSet($supplier, $request, 'taxcode');
        $this->setIfSet($supplier, $request, 'vat');
        $this->setIfSet($supplier, $request, 'description');
        $this->setIfSet($supplier, $request, 'payment_method');
        $this->setIfSet($supplier, $request, 'order_method');
        $this->boolIfSet($supplier, $request, 'fast_shipping_enabled');
        $this->boolIfSet($supplier, $request, 'unmanaged_shipping_enabled');

        if (isset($request['status'])) {
            $supplier->setStatus($request['status'], $request['deleted_at'], $request['suspended_at']);
        }
    }

    public function store(array $request)
    {
        $creator = $this->ensureAuth(['supplier.add' => 'gas']);

        if (!isset($request['payment_method']) || is_null($request['payment_method'])) {
            $request['payment_method'] = '';
		}

        if (!isset($request['order_method']) || is_null($request['order_method'])) {
            $request['order_method'] = '';
		}

        $supplier = new Supplier();
        $this->setCommonAttributes($supplier, $request);
        $supplier->save();

        return $supplier;
    }

    public function update($id, array $request)
    {
        $supplier = $this->show($id);
        $this->ensureAuth(['supplier.modify' => $supplier]);

        try {
            $supplier = DB::transaction(function () use ($supplier, $request) {
                $this->setCommonAttributes($supplier, $request);
                $supplier->save();
                $supplier->updateContacts($request);
                return $supplier;
            });
        }
        catch(\Exception $e) {
            Log::error('Errore aggiornamento fornitore: ' . $e->getMessage() . ' - ' . print_r($request, true));
            $supplier = null;
        }

        return $supplier;
    }

    public function catalogue($id, $format, array $request)
    {
        $this->ensureAuth();

        $format = $format ?: $request['format'];
        $supplier = $this->show($id);

        if ($format == 'gdxp') {
            return redirect($supplier->exportableURL());
        }

        /*
            Questi sono i dati di default, da usare quando si fa il download del
            listino come allegato al fornitore (e dunque non si passa per il
            pannello di selezione dei campi).
            Cfr. Supplier::defaultAttachments()
        */
        $fields = $request['fields'] ?? ['name', 'measure', 'price', 'active'];

        $filename = sanitizeFilename(_i('Listino %s.%s', [$supplier->name, $format]));

        if (isset($request['printable'])) {
            $products = $supplier->products()->whereIn('id', $request['printable'])->sorted()->get();
        }
        else {
            $products = $supplier->products()->sorted()->get();
        }

        $headers = ProductFormatter::getHeaders($fields);
        $data = ProductFormatter::formatArray($products, $fields);

        if ($format == 'pdf') {
            $pdf = PDF::loadView('documents.cataloguepdf', ['supplier' => $supplier, 'headers' => $headers, 'data' => $data]);
            return $pdf->download($filename);
        }
        elseif ($format == 'csv') {
            return output_csv($filename, $headers, $data, null);
        }
    }

    public function destroy($id)
    {
        $supplier = DB::transaction(function () use ($id) {
            $supplier = $this->show($id);

            if ($supplier->trashed()) {
                $this->ensureAuth(['supplier.add' => 'gas']);

                foreach($supplier->products()->withTrashed()->get() as $product) {
                    $product->forceDelete();
                }

                $supplier->forceDelete();
            }
            else {
                $this->ensureAuth(['supplier.modify' => $supplier]);
                $supplier->delete();
            }

            return $supplier;
        });

        return $supplier;
    }
}
