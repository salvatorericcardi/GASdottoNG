<x-larastrap::text name="name" :label="_i('Nome')" required />
<x-larastrap::price name="price" :label="_i('Prezzo Unitario')" required :pophelp="_i('Prezzo unitario per unità di misura. Si intende IVA inclusa, per maggiori dettagli si veda il campo Aliquota IVA. Può assumere un significato particolare quando viene attivata la Pezzatura')" />
<x-larastrap::selectobj name="category_id" :label="_i('Categoria')" :options="App\Category::with(['children'])->orderBy('name', 'asc')->where('parent_id', '=', null)->get()" required />
<x-larastrap::selectobj name="measure_id" :label="_i('Unità di Misura')" classes="measure-selector" :options="App\Measure::orderBy('name', 'asc')->get()" required :help="_i('Hai selezionato una unità di misura discreta: per questo prodotto possono essere usate solo quantità intere.')" :pophelp="_i('Unità di misura assegnata al prodotto. Attenzione: può influenzare l\'abilitazione di alcune variabili del prodotto, si veda il parametro Unità Discreta nel pannello di amministrazione delle unità di misura (acessibile solo agli utenti abilitati)')" />
<x-larastrap::textarea name="description" :label="_i('Descrizione')" />
<x-larastrap::selectobj name="vat_rate_id" :label="_i('Aliquota IVA')" :options="App\VatRate::orderBy('name', 'asc')->get()" :pophelp="_i('Le aliquote esistenti possono essere configurate nel pannello Configurazioni')" :extraitem="_i('Nessuna')" />
