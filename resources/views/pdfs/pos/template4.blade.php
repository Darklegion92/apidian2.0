<!DOCTYPE html>
<html lang="es">
{{-- <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>FACTURA ELECTRONICA Nro: {{$resolution->prefix}} - {{$request->number}}</title>
</head> --}}

{{-- Header incluido en el template--}}

<!-- Encabezado: Información de la Empresa y Resolución (centrado) -->
<table style="width: 100%; font-size: 9px;">
    <!-- Logo en la parte superior -->
    <tr>
        <td style="text-align: center;">
            <img style="max-width: 170px; height: auto; margin-bottom: 5px;" src="{{$imgLogo}}" alt="logo">
        </td>
    </tr>
    <!-- Información de la Empresa -->
    <tr>
        <td style="text-align: center;">
            <strong>{{$user->name}}</strong><br>
            @if(isset($request->establishment_name) && $request->establishment_name != 'Oficina Principal')
                <strong>{{$request->establishment_name}}</strong><br>
            @endif
            <strong>NIT: {{$company->identification_number}}-{{$company->dv}} - Dirección: {{$company->address}}</strong><br>
            <strong>Tel: {{$company->phone}} - Correo: {{$user->email}}</strong>
        </td>
    </tr>
    <!-- Información Adicional y Condiciones, Resolución -->
    <tr>
        <td style="text-align: center; margin-top: 5px;">
            <strong>Regimen: {{$company->type_regime->name}}</strong> -
            <strong>Obligacion: {{$company->type_liability->name}}</strong>
            @if(isset($request->nombretipodocid))
                - <strong>Tipo Documento ID: {{$request->nombretipodocid}}</strong>
            @endif
            @if(isset($request->tarifaica) && $request->tarifaica != '100')
                - <strong>TARIFA ICA: {{$request->tarifaica}}%</strong>
            @endif
            @if(isset($request->actividadeconomica))
                - <strong>ACTIVIDAD ECONOMICA: {{$request->actividadeconomica}}</strong>
            @endif
            @if(isset($request->seze))
                <?php
                    $aseze = substr($request->seze, 0, strpos($request->seze, '-', 0));
                    $asociedad = substr($request->seze, strpos($request->seze, '-', 0) + 1);
                ?>
                - <strong>Regimen SEZE Año: {{$aseze}} Constitución Sociedad Año: {{$asociedad}}</strong>
            @endif
            <br>
            <strong>Resolución de Facturación Electrónica No. {{$resolution->resolution}} de {{$resolution->resolution_date}}</strong><br>
            <strong>Prefijo: {{$resolution->prefix}}, Rango {{$resolution->from}} al {{$resolution->to}}</strong><br>
            <strong>Vigencia Desde: {{$resolution->date_from}} Hasta: {{$resolution->date_to}}</strong><br>
            @if (isset($request->seze))
                <strong>FAVOR ABSTENERSE DE PRACTICAR RETENCIÓN EN LA FUENTE REGIMEN ESPECIAL DECRETO 2112 DE 2019</strong>
            @endif
        </td>
    </tr>
    <!-- Información de Contacto del Establecimiento
    <tr>
        <td style="text-align: center; margin-top: 5px;">
            @if(isset($request->establishment_address))
                <strong>{{$request->establishment_address}}</strong> -
            @else
                <strong>{{$company->address}}</strong> -
            @endif
            @inject('municipality', 'App\Municipality')
            @if(isset($request->establishment_municipality))
                <strong>{{$municipality->findOrFail($request->establishment_municipality)['name']}} - {{$municipality->findOrFail($request->establishment_municipality)['department']['name']}}</strong> -
            @else
                <strong>{{$company->municipality->name}} - {{$municipality->findOrFail($company->municipality->id)['department']['name']}}</strong> -
            @endif
            {{$company->country->name}}<br>
            @if(isset($request->establishment_phone))
                <strong>Teléfono: {{$request->establishment_phone}}</strong> -
            @else
                <strong>Teléfono: {{$company->phone}}</strong> -
            @endif
            @if(isset($request->establishment_email))
                <strong>E-mail: {{$request->establishment_email}}</strong>
            @else
                <strong>E-mail: {{$user->email}}</strong>
            @endif
        </td>
    </tr>
    -->
</table>

<!-- Detalles de la Factura (alineados a la izquierda) -->
<table style="width: 100%; font-size: 9px; margin-top: 10px;">
    <tr>
        <td style="text-align: left;">
            <strong>FACTURA ELECTRONICA DE VENTA {{$resolution->prefix}} - {{$request->number}}</strong><br>
            <strong>Fecha Emisión: {{$date}}</strong><br>
            <strong>Fecha Validación DIAN: {{$date}}</strong><br>
            <strong>Hora Validación DIAN: {{$time}}</strong>
        </td>
    </tr>
</table>



{{--Fin del Header--}}

<hr>

<body>
<!-- Información del Cliente -->
<table style="width: 100%; font-size: 10px; margin-top: 5px; border-collapse: collapse;">
    <tr>
        <td style="text-align: left; padding: 3px 0;">
            <span><strong>CC o NIT:</strong> {{$customer->company->identification_number}}-{{$request->customer['dv'] ?? NULL}}</span> <br>
            <span style="margin-left: 10px;"><strong>Cliente:</strong><b>{{$customer->name}}</b></span> <br>
            <span style="margin-left: 10px;"><strong>Régimen:</strong> {{$customer->company->type_regime->name}}</span>
            <span style="margin-left: 10px;"><strong>Obligación:</strong> {{$customer->company->type_liability->name}}</span> <br>
            <span style="margin-left: 10px;"><strong>Dirección:</strong> {{$customer->company->address}}</span> <br>
            <span style="margin-left: 10px;"><strong>Ciudad:</strong>
                @if($customer->company->country->id == 46)
                    {{$customer->company->municipality->name}} - {{$customer->company->country->name}}
                @else
                    {{$customer->company->municipality_name}} - {{$customer->company->state_name}} - {{$customer->company->country->name}}
                @endif
            </span>
            <span style="margin-left: 10px;"><strong>Teléfono:</strong> {{$customer->company->phone}}</span> <br>
            <span style="margin-left: 10px;"><strong>Email:</strong> {{$customer->email}}</span>
        </td>
    </tr>
</table>

<!-- Información de Pago y Referencias -->
<table style="width: 100%; font-size: 10px; margin-top: 5px; border-collapse: collapse;">
    <!-- Línea principal -->
    <tr>
        <td style="text-align: left; padding: 3px 0;">
            <span><strong>Forma de Pago:</strong> {{$paymentForm[0]->name}}</span>
            <span style="margin-left: 10px;"><strong>Medios de Pago:</strong>
                @foreach ($paymentForm as $paymentF)
                    {{$paymentF->nameMethod}}{{ !$loop->last ? ', ' : '' }}
                @endforeach
            </span> <br>
            <span style="margin-left: 10px;"><strong>Plazo Para Pagar:</strong> {{$paymentForm[0]->duration_measure}} Días</span> <br>
            <span style="margin-left: 10px;"><strong>Fecha Vencimiento:</strong> {{$paymentForm[0]->payment_due_date}}</span>
        </td>
    </tr>
    @if($request['currency_id'] != 35 && $request['currency_id'] !== null)
        @inject('currency', 'App\TypeCurrency')
        <tr>
            <td style="padding: 0; width: 50%;">Tipo Moneda:</td>
            <td style="padding: 0;">{{$currency->where('id', 'like', $request['currency_id'].'%')->firstOrFail()['name']}}</td>
        </tr>
        <tr>
            <td style="padding: 0; width: 50%;">T.R.M:</td>
            <td style="padding: 0;">{{number_format($request['calculationrate'], 2)}}</td>
        </tr>
    @endif
    <!-- Línea extra opcional -->
    @if(isset($request['order_reference']['id_order']) ||
        isset($request['order_reference']['issue_date_order']) ||
        isset($healthfields) ||
        isset($request['number_account']) ||
        isset($request['deliveryterms']))
    <tr>
        <td style="text-align: left; padding: 3px 0;">
            @if(isset($request['order_reference']['id_order']))
                <span><strong>Número Pedido:</strong> {{$request['order_reference']['id_order']}}</span>
            @endif
            @if(isset($request['order_reference']['issue_date_order']))
                <span style="margin-left: 10px;"><strong>Fecha Pedido:</strong> {{$request['order_reference']['issue_date_order']}}</span>
            @endif
            @if(isset($healthfields))
                <span style="margin-left: 10px;"><strong>Inicio Periodo Facturación:</strong> {{$healthfields->invoice_period_start_date}}</span>
                <span style="margin-left: 10px;"><strong>Fin Periodo Facturación:</strong> {{$healthfields->invoice_period_end_date}}</span>
            @endif
            @if(isset($request['number_account']))
                <span style="margin-left: 10px;"><strong>Número de cuenta:</strong> {{$request['number_account']}}</span>
            @endif
            @if(isset($request['deliveryterms']))
                <span style="margin-left: 10px;"><strong>Términos de Entrega:</strong> {{$request['deliveryterms']['loss_risk_responsibility_code']}} - {{$request['deliveryterms']['loss_risk']}}</span>
                <span style="margin-left: 10px;"><strong>T.R.M:</strong> {{ number_format($request['calculationrate'], 2) }}</span>
                <span style="margin-left: 10px;"><strong>Fecha T.R.M:</strong> {{$request['calculationratedate']}}</span>
                <span style="margin-left: 10px;"><strong>Tipo Moneda:</strong>
                    @inject('currency', 'App\TypeCurrency')
                    {{$currency->findOrFail($request['idcurrency'])['name']}}
                </span>
            @endif
        </td>
    </tr>
    @endif
</table>


    <hr>

    @isset($healthfields)
        <table class="table" style="width: 100%; font-weight: bold;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 100%;">INFORMACION REFERENCIAL SECTOR SALUD</th>
                </tr>
            </thead>
        </table>
        <table class="table" style="width: 100%">
    <thead>
        <th class="text-center" style="width: 12%;">Cod Prestador</th>
        <th class="text-center" style="width: 29%;">Info. Contrat.</th>
        <th class="text-center" style="width: 18%;">Info. de Pagos</th>
    </thead>
    <tbody>
        @foreach ($healthfields->user_info as $item)
        <tr>
            <td style="font-size: 8px;">{{$item->provider_code}}</td>
            <td>
                <p style="font-size: 8px">Modalidad Contratacion: {{$item->health_contracting_payment_method()->name}}</p>
                <p style="font-size: 8px">Nro Contrato: {{$item->contract_number}}</p>
                <p style="font-size: 8px">Cobertura: {{$item->health_coverage()->name}}</p>
            </td>
            <td>
                <p style="font-size: 8px">Copago: {{number_format($item->co_payment, 2)}}</p>
                <p style="font-size: 8px">Cuota Moderardora: {{number_format($item->moderating_fee, 2)}}</p>
                <p style="font-size: 8px">Pagos Compartidos: {{number_format($item->shared_payment, 2)}}</p>
                <p style="font-size: 8px">Anticipos: {{number_format($item->advance_payment, 2)}}</p>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

        <br>
    @endisset


            <table class="tabla-items" style="font-size: 10px; width:100%;">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $ItemNro = 0; ?>
                @foreach($request['invoice_lines'] as $item)
                    <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        <td>
                            @inject('um', 'App\UnitMeasure')
                            @if($item['description'] == 'Administración' or $item['description'] == 'Imprevisto' or $item['description'] == 'Utilidad')
                                <!-- Para ítems especiales se muestran guiones en cantidad y UM -->
                                <strong>{{$ItemNro}} | {{$item['description']}}</strong>
                                | -
                                | -
                                | Val. Unit: {{ number_format($item['price_amount'], 2) }}
                                | IVA: {{ number_format($item['tax_totals'][0]['tax_amount'], 2) }}
                            @else
                                <!-- Para ítems normales se muestran todos los datos en una misma línea -->
                                <strong>{{$ItemNro}} | {{$item['description']}}</strong>
                                @if(isset($item['notes']))
                                    | {{$item['notes']}}
                                @endif
                                | {{ $um->findOrFail($item['unit_measure_id'])['name'] }} : {{ number_format($item['invoiced_quantity'], 2) }}
                                | Val. Unit:
                                    @if(isset($item['allowance_charges']))
                                        {{ number_format(($item['line_extension_amount'] + $item['allowance_charges'][0]['amount']) / $item['invoiced_quantity'], 2) }}
                                    @else
                                        {{ number_format($item['line_extension_amount'] / $item['invoiced_quantity'], 2) }}
                                    @endif
                                | IVA:
                                    @if(isset($item['tax_totals']))
                                        @if(isset($item['tax_totals'][0]['tax_amount']))
                                            {{ number_format($item['tax_totals'][0]['tax_amount'] / $item['invoiced_quantity'], 2) }}
                                        @else
                                            {{ number_format(0, 2) }}
                                        @endif
                                    @else
                                        E
                                    @endif
                                    |
                                    @if(isset($item['allowance_charges']))
                                       DESCUENTO:
                                       {{number_format(($item['allowance_charges'][0]['amount'] * 100) / $item['allowance_charges'][0]['base_amount'], 2)}}%
                                       ${{number_format($item['allowance_charges'][0]['amount'] / $item['invoiced_quantity'], 2)}}
                                    @else
                                        {{number_format("0", 2)}}
                                        {{number_format("0", 2)}}
                                       {{number_format($item['invoiced_quantity'] * ($item['line_extension_amount'] / $item['invoiced_quantity']), 2)}}
                                    @endif
                            @endif
                        </td>
                        <td class="text-right">
                            @if($item['description'] == 'Administración' or $item['description'] == 'Imprevisto' or $item['description'] == 'Utilidad')
                                {{ number_format($item['invoiced_quantity'] * $item['price_amount'], 2) }}
                            @else
                                @if(isset($item['allowance_charges']))
                                    @if(isset($item['tax_totals']))
                                        {{ number_format($item['line_extension_amount'] + $item['tax_totals'][0]['tax_amount'], 2) }}
                                    @else
                                        {{ number_format($item['line_extension_amount'], 2) }}
                                    @endif
                                @else
                                    {{ number_format($item['line_extension_amount'], 2) }}
                                @endif
                            @endif
                        </td>
                    </tr>
                    <!-- Línea punteada separadora a lo ancho de la tabla -->
                    <tr>
                        <td colspan="2" style="padding: 0; margin: 0;">
                        <hr style="border: 0; border-top: 1px dashed #000; margin: 0;">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Tabla para IVA y Retenciones -->
        <table class="tabla-impuestos" style="width:100%; font-size: 10px; margin-top: 8px;">
            <tr>
                <!-- Columna de IVA -->
                <td style="width:50%; vertical-align: top; border-right: 1px solid #ccc; padding-right: 10px;">
                    <div style="text-align: center; font-weight: bold; margin-bottom: 5px;">IVA</div>
                    @if(isset($request->tax_totals))
                        <?php $TotalImpuestos = 0; ?>
                        @foreach($request->tax_totals as $item)
                            <?php $TotalImpuestos += $item['tax_amount']; ?>
                            @inject('tax', 'App\Tax')
                            <div style="padding: 3px 0;">
                                {{$tax->findOrFail($item['tax_id'])['name']}} ({{ number_format($item['percent'], 2) }}%):
                                {{ number_format($item['tax_amount'], 2) }}
                            </div>
                        @endforeach
                    @else
                        <div style="text-align: center;">No hay impuestos</div>
                    @endif
                </td>

                <!-- Columna de Retenciones -->
                <td style="width:50%; vertical-align: top; padding-left: 10px;">
                    <div style="text-align: center; font-weight: bold; margin-bottom: 5px;">Retenciones</div>
                    @if(isset($withHoldingTaxTotal))
                        <?php $TotalRetenciones = 0; ?>
                        @foreach($withHoldingTaxTotal as $item)
                            <?php $TotalRetenciones += $item['tax_amount']; ?>
                            @inject('tax', 'App\Tax')
                            <div style="padding: 3px 0;">
                                {{$tax->findOrFail($item['tax_id'])['name']}}:
                                {{ number_format($item['tax_amount'], 2) }}
                            </div>
                        @endforeach
                    @else
                        <div style="text-align: center;">No hay retenciones</div>
                    @endif
                </td>
            </tr>
        </table>

            <!-- Tabla para Totales -->
            <table class="tabla-totales" style="margin-top: 8px; width: 100%;">
                <tr>
                    <th style="text-align: left; font-weight: normal;">Nro Lineas</th>
                    <td style="text-align: right; font-weight: normal;">{{$ItemNro}}</td>
                </tr>
                <tr>
                    <th style="text-align: left; font-weight: normal;">Base</th>
                    <td style="text-align: right; font-weight: normal;">{{ number_format($request->legal_monetary_totals['line_extension_amount'], 2) }}</td>
                </tr>
                <tr>
                    <th style="text-align: left; font-weight: normal;">Impuestos</th>
                    <td style="text-align: right; font-weight: normal;">{{ number_format($TotalImpuestos, 2) }}</td>
                </tr>
                <tr>
                    <th style="text-align: left; font-weight: normal;">Retenciones</th>
                    <td style="text-align: right; font-weight: normal;">{{ number_format($TotalRetenciones, 2) }}</td>
                </tr>
                @if(isset($request->legal_monetary_totals['allowance_total_amount']))
                    <tr>
                        <th style="text-align: left; font-weight: normal;">Descuentos</th>
                        <td style="text-align: right; font-weight: normal;">{{ number_format($request->legal_monetary_totals['allowance_total_amount'], 2) }}</td>
                    </tr>
                @endif
                @if(isset($request->previous_balance) && $request->previous_balance > 0)
                    <tr>
                        <th style="text-align: left; font-weight: normal;">Saldo Anterior</th>
                        <td style="text-align: right; font-weight: normal;">{{ number_format($request->previous_balance, 2) }}</td>
                    </tr>
                @endif
                <!-- Calculo de Total Factura - Descuentos -->
                <tr>
                    <td style="text-align: left; font-weight: normal;">Total Factura - Descuentos:</td>
                    @if(isset($request->tarifaica))
                        @if(isset($request->legal_monetary_totals['allowance_total_amount']))
                            @if(isset($request->previous_balance))
                                <td style="text-align: right; font-weight: normal;">{{ number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance - $TotalRetenciones, 2) }}</td>
                            @else
                                <td style="text-align: right; font-weight: normal;">{{ number_format($request->legal_monetary_totals['payable_amount'] - $TotalRetenciones, 2) }}</td>
                            @endif
                        @else
                            @if(isset($request->previous_balance))
                                <td style="text-align: right; font-weight: normal;">{{ number_format($request->legal_monetary_totals['payable_amount'] + 0 + $request->previous_balance - $TotalRetenciones, 2) }}</td>
                            @else
                                <td style="text-align: right; font-weight: normal;">{{ number_format($request->legal_monetary_totals['payable_amount'] + 0 - $TotalRetenciones, 2) }}</td>
                            @endif
                        @endif
                    @else
                        @if(isset($request->previous_balance))
                            <td style="text-align: right; font-weight: normal;">{{ number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance - $TotalRetenciones, 2) }}</td>
                        @else
                            <td style="text-align: right; font-weight: normal;">{{ number_format($request->legal_monetary_totals['payable_amount'] - $TotalRetenciones, 2) }}</td>
                        @endif
                    @endif
                </tr>
                <!-- Total a Pagar -->
                <tr>
                    <td style="text-align: left; font-weight: bold; font-size: 16px;">Total a Pagar</td>
                    @if(isset($request->tarifaica))
                        @if(isset($request->legal_monetary_totals['allowance_total_amount']))
                            @if(isset($request->previous_balance))
                                <td style="text-align: right; font-size: 16px; font-weight: bold;">{{ number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance - $TotalRetenciones, 2) }}</td>
                            @else
                                <td style="text-align: right; font-size: 16px; font-weight: bold;">{{ number_format($request->legal_monetary_totals['payable_amount'] - $TotalRetenciones, 2) }}</td>
                            @endif
                        @else
                            @if(isset($request->previous_balance))
                                <td style="text-align: right; font-size: 16px; font-weight: bold;">{{ number_format($request->legal_monetary_totals['payable_amount'] + 0 + $request->previous_balance - $TotalRetenciones, 2) }}</td>
                            @else
                                <td style="text-align: right; font-size: 16px; font-weight: bold;">{{ number_format($request->legal_monetary_totals['payable_amount'] + 0 - $TotalRetenciones, 2) }}</td>
                            @endif
                        @endif
                    @else
                        @if(isset($request->previous_balance))
                            <td style="text-align: right; font-size: 16px; font-weight: bold;">{{ number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance - $TotalRetenciones, 2) }}</td>
                        @else
                            <td style="text-align: right; font-size: 16px; font-weight: bold;">{{ number_format($request->legal_monetary_totals['payable_amount'] - $TotalRetenciones, 2) }}</td>
                        @endif
                    @endif
                </tr>
            </table>


            @inject('Varios', 'App\Custom\NumberSpellOut')
            <div class="text-right" style="margin-top: -25px; font-weight: bold;">
                <div>
                    <p style="font-size: 12pt">
                        @php
                            // Inicializamos con payable_amount
                            $totalAmount = $request->legal_monetary_totals['payable_amount'];

                            // Verificamos si existe previous_balance
                            if (isset($request->previous_balance)) {
                                $totalAmount += $request->previous_balance;
                            }

                            // Verificamos si existen retenciones y las restamos
                            if (isset($TotalRetenciones)) {
                                $totalAmount -= $TotalRetenciones;
                            }

                            // Finalmente, redondeamos el total a dos decimales
                            $totalAmount = round($totalAmount, 2);

                            // Definimos la moneda
                            $idcurrency = $request->idcurrency ?? null;
                        @endphp
                        <p><strong>SON</strong>: {{$Varios->convertir($totalAmount, $idcurrency)}} M/CTE*********.</p>
                    </p>
                </div>
            </div>


        @if(isset($notes))
        <div class="summarys">
            <div class="text-word" id="note">
                <p><strong>NOTAS:</strong></p>
                <p style="font-style: italic; font-size: 10px; font-weight: bold;">{{$notes}}</p>
            </div>
        </div>
        @endif

    {{--
    <div class="summary" >
        <div class="text-word" id="note">
            @if(isset($request->disable_confirmation_text))
                @if(!$request->disable_confirmation_text)
                    <p style="font-style: italic;">INFORME EL PAGO AL TELEFONO {{$company->phone}} o al e-mail {{$user->email}}<br>
                        {{-- <br>
                        <div id="firma">
                            <p><strong>FIRMA ACEPTACIÓN:</strong></p><br>
                            <p><strong>CC:</strong></p><br>
                            <p><strong>FECHA:</strong></p><br>
                        </div>
                    </p>
                @endif
            @endif
        </div>
        @if(isset($firma_facturacion) and !is_null($firma_facturacion))
            <table style="font-size: 10px">
                <tr>
                    <td class="vertical-align-top" style="width: 50%; text-align: right">
                        <img style="width: 250px;" src="{{$firma_facturacion}}">
                    </td>
                </tr>
            </table>
        @endif
    </div>

    --}}

    <!-- Footer -->
    <div id="footer" style="font-size: 13px; text-align: center; margin-top: -10px; font-weight: bold;">
        <hr style="margin-bottom: 4px;">
        <p id='mi-texto'>
            Factura No: {{$resolution->prefix}} - {{$request->number}}<br>
            Fecha y Hora de Generación: {{$date}} - {{$time}}<br>
            <strong> CUFE: {{$cufecude}}</strong>
        </p>

        <div style="text-align: center;">
            <img style="width: 70%;" src="{{$imageQr}}">
        </div>
    </div>

    <div id="footer" style="font-size: 13px; text-align: center;">
        @isset($request->foot_note)
            <p id='mi-texto-1'>{{$request->foot_note}}</p>
        @endisset

        <h3> GRACIAS POR SU COMPRA</h3>
    </div>
</body>
</html>
