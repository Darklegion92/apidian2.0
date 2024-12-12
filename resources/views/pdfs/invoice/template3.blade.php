<!DOCTYPE html>
<html lang="es">
{{-- <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>FACTURA ELECTRONICA Nro: {{$resolution->prefix}} - {{$request->number}}</title>
</head> --}}

{{-- Header incluido en el template--}}

<table style="width: 100%; font-size: 9px; font-weight: bold;">
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
            <strong>Tel: {{$company->phone}} - Correo: {{$user->email}}</strong><br>
        </td>
    </tr>

    <!-- Detalles de la Factura -->
    <tr>
        <td style="text-align: center;">
            <strong>FACTURA ELECTRONICA DE VENTA {{$resolution->prefix}} - {{$request->number}}</strong><br>
            <strong>Fecha Emisión: {{$date}} - Fecha Validación DIAN: {{$date}}</strong><br>
            <strong>Hora Validación DIAN: {{$time}}</strong><br>
        </td>
    </tr>

    <!-- Información Adicional y Condiciones -->
    <tr>
        <td style="text-align: center;">
            @if(isset($request->ivaresponsable) && $request->ivaresponsable != $company->type_regime->name)
                <strong>{{$company->type_regime->name}} - {{$request->ivaresponsable}}</strong><br>
            @endif
            @if(isset($request->nombretipodocid))
                <strong>Tipo Documento ID: {{$request->nombretipodocid}}</strong><br>
            @endif
            @if(isset($request->tarifaica) && $request->tarifaica != '100')
                <strong>TARIFA ICA: {{$request->tarifaica}}%</strong><br>
            @endif
            @if(isset($request->actividadeconomica))
                <strong>ACTIVIDAD ECONOMICA: {{$request->actividadeconomica}}</strong><br>
            @endif
            @if(isset($request->seze))
                <?php
                    $aseze = substr($request->seze, 0, strpos($request->seze, '-', 0));
                    $asociedad = substr($request->seze, strpos($request->seze, '-', 0) + 1);
                ?>
                <strong>Regimen SEZE Año: {{$aseze}} Constitución Sociedad Año: {{$asociedad}}</strong><br>
            @endif
            <strong>Resolución de Facturación Electrónica No. {{$resolution->resolution}} de {{$resolution->resolution_date}}</strong><br>
            <strong>Prefijo: {{$resolution->prefix}}, Rango {{$resolution->from}} al {{$resolution->to}}</strong><br>
            <strong>Vigencia Desde: {{$resolution->date_from}} Hasta: {{$resolution->date_to}}</strong><br>
            @if (isset($request->seze))
                <strong>FAVOR ABSTENERSE DE PRACTICAR RETENCION EN LA FUENTE REGIMEN ESPECIAL DECRETO 2112 DE 2019</strong><br>
            @endif
        </td>
    </tr>

    <!-- Información de Contacto del Establecimiento -->
    <tr>
        <td style="text-align: center;">
            @if(isset($request->establishment_address))
                <strong>{{$request->establishment_address}} -</strong>
            @else
                <strong>{{$company->address}} -</strong>
            @endif
            @inject('municipality', 'App\Municipality')
            @if(isset($request->establishment_municipality))
                <strong>{{$municipality->findOrFail($request->establishment_municipality)['name']}} - {{$municipality->findOrFail($request->establishment_municipality)['department']['name']}} -</strong>
            @else
                <strong>{{$company->municipality->name}} - {{$municipality->findOrFail($company->municipality->id)['department']['name']}} -</strong>
            @endif
            {{$company->country->name}}<br>
            @if(isset($request->establishment_phone))
                <strong>Teléfono: {{$request->establishment_phone}}</strong><br>
            @else
                <strong>Teléfono: {{$company->phone}}</strong><br>
            @endif
            @if(isset($request->establishment_email))
                <strong>E-mail: {{$request->establishment_email}}</strong><br>
            @else
                <strong>E-mail: {{$user->email}}</strong><br>
            @endif
        </td>
    </tr>
</table>


{{--Fin del Header--}}

<hr>

<body>
    <table style="font-size: 13px; font-weight: bold;" >
        <tr>
            <td class="vertical-align-top" style="width: 60%;">
                <table>
                    <tr>
                        <td><strong>CC o NIT:</strong></td>
                        <td><strong>{{$customer->company->identification_number}}-{{$request->customer['dv'] ?? NULL}} </strong></td>
                    </tr>
                    <tr>
                        <td><strong>Cliente:</strong></td>
                        <td><strong>{{$customer->name}}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Régimen:</strong></td>
                        <td><strong>{{$customer->company->type_regime->name}}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Obligación:</strong></td>
                        <td><strong>{{$customer->company->type_liability->name}}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Dirección:</strong></td>
                        <td><strong>{{$customer->company->address}}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Ciudad:</td>
                        @if($customer->company->country->id == 46)
                            <td><strong>{{$customer->company->municipality->name}} - {{$customer->company->country->name}} </strong></td>
                        @else
                            <td><strong>{{$customer->company->municipality_name}} - {{$customer->company->state_name}} - {{$customer->company->country->name}} </strong></td>
                        @endif
                    </tr>
                    <tr>
                        <td><strong>Teléfono:</strong></td>
                        <td><strong>{{$customer->company->phone}}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Email:</td>
                        <td><strong>{{$customer->email}}</strong></td>
                    </tr>
                </table>
            </td>
            <td class="vertical-align-top" style="width: 40%; padding-left: 1rem">
                <table>
                    <tr>
                        <td><strong>Forma de Pago:</strong></td>
                        <td><strong>{{$paymentForm[0]->name}}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Medios de Pago:</td>
                        <td>
                            @foreach ($paymentForm as $paymentF)
                                <strong>{{$paymentF->nameMethod}}</strong><br>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Plazo Para Pagar:</strong></td>
                        <td><strong>{{$paymentForm[0]->duration_measure}} Dias</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Fecha Vencimiento:</strong></td>
                        <td><strong>{{$paymentForm[0]->payment_due_date}}</strong></td>
                    </tr>
                    @if(isset($request['order_reference']['id_order']))
                    <tr>
                        <td><strong>Número Pedido:</strong></td>
                        <td><strong>{{$request['order_reference']['id_order']}}</strong></td>
                    </tr>
                    @endif
                    @if(isset($request['order_reference']['issue_date_order']))
                    <tr>
                        <td><strong>Fecha Pedido:</strong></td>
                        <td><strong>{{$request['order_reference']['issue_date_order']}}</strong></td>
                    </tr>
                    @endif
                    @if(isset($healthfields))
                    <tr>
                        <td><strong>Inicio Periodo Facturación:</strong></td>
                        <td><strong>{{$healthfields->invoice_period_start_date}}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Fin Periodo Facturación:</strong></td>
                        <td><strong>{{$healthfields->invoice_period_end_date}}</strong></td>
                    </tr>
                    @endif
                    @if(isset($request['number_account']))
                    <tr>
                        <td><strong>Número de cuenta:</strong></td>
                        <td><strong>{{$request['number_account'] }}</strong></td>
                    </tr>
                    @endif
                    @if(isset($request['deliveryterms']))
                    <tr>
                        <td><strong>Terminos de Entrega:</strong></td>
                        <td><strong>{{$request['deliveryterms']['loss_risk_responsibility_code']}} - {{ $request['deliveryterms']['loss_risk'] }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>T.R.M:</strong></td>
                        <td><strong>{{number_format($request['calculationrate'], 2)}}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Fecha T.R.M:</strong></td>
                        <td><strong>{{$request['calculationratedate']}}</strong></td>
                    </tr>
                    <tr>
                        @inject('currency', 'App\TypeCurrency')
                        <td><strong>Tipo Moneda:</strong></td>
                        <td><strong>{{$currency->findOrFail($request['idcurrency'])['name']}}</strong></td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
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


        <table class="tabla-items" style="font-weight: bold;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Código</th>
                    <th class="desc">Descripción</th>
                    <th>Cant.</th>
                    <th>UM</th>
                    <th>Val. Unit</th>
                    <th>IVA/IC</th>
                    <th>Dcto</th>
                    <th>%</th>
                    <th>Val. Item</th>
                </tr>
            </thead>
            <tbody>
                <?php $ItemNro = 0; ?>
                @foreach($request['invoice_lines'] as $item)
                    <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        @inject('um', 'App\UnitMeasure')
                        @if($item['description'] == 'Administración' or $item['description'] == 'Imprevisto' or $item['description'] == 'Utilidad')
                            <td>{{$ItemNro}}</td>
                            <td class="text-right">
                                {{$item['code']}}
                            </td>
                            <td>{{$item['description']}}</td>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                            <td class="text-right">{{number_format($item['price_amount'], 2)}}</td>
                            <td class="text-right">{{number_format($item['tax_totals'][0]['tax_amount'], 2)}}</td>
                            @if(isset($item['allowance_charges']))
                                <td class="text-right">{{number_format($item['allowance_charges'][0]['amount'], 2)}}</td>
                                <td class="text-right">{{number_format(($item['allowance_charges'][0]['amount'] * 100) / $item['allowance_charges'][0]['base_amount'], 2)}}</td>
                            @else
                                <td class="text-right">{{number_format("0", 2)}}</td>
                                <td class="text-right">{{number_format("0", 2)}}</td>
                            @endif
                            <td class="text-right">{{number_format($item['invoiced_quantity'] * $item['price_amount'], 2)}}</td>
                        @else
                            <td>{{$ItemNro}}</td>
                            <td>{{$item['code']}}</td>
                            <td>
                                @if(isset($item['notes']))
                                    {{$item['description']}}
                                    <p style="font-size: 10px">{{$item['notes']}}</p>
                                @else
                                    {{$item['description']}}
                                @endif
                            </td>
                            <td class="text-right">{{number_format($item['invoiced_quantity'], 2)}}</td>
                            <td class="text-right">{{$um->findOrFail($item['unit_measure_id'])['name']}}</td>

                            @if(isset($item['tax_totals']))
                                @if(isset($item['allowance_charges']))
                                    <td class="text-right">{{number_format(($item['line_extension_amount'] + $item['allowance_charges'][0]['amount']) / $item['invoiced_quantity'], 2)}}</td>
                                @else
                                    <td class="text-right">{{number_format($item['line_extension_amount'] / $item['invoiced_quantity'], 2)}}</td>
                                @endif
                            @else
                                @if(isset($item['allowance_charges']))
                                    <td class="text-right">{{number_format(($item['line_extension_amount'] + $item['allowance_charges'][0]['amount']) / $item['invoiced_quantity'], 2)}}</td>
                                @else
                                    <td class="text-right">{{number_format($item['line_extension_amount'] / $item['invoiced_quantity'], 2)}}</td>
                                @endif
                            @endif

                            @if(isset($item['tax_totals']))
                                @if(isset($item['tax_totals'][0]['tax_amount']))
                                    <td class="text-right">{{number_format($item['tax_totals'][0]['tax_amount'] / $item['invoiced_quantity'], 2)}}</td>
                                @else
                                    <td class="text-right">{{number_format(0, 2)}}</td>
                                @endif
                            @else
                                <td class="text-right">E</td>
                            @endif

                            @if(isset($item['allowance_charges']))
                                <td class="text-right">{{number_format($item['allowance_charges'][0]['amount'] / $item['invoiced_quantity'], 2)}}</td>
                                <td class="text-right">{{number_format(($item['allowance_charges'][0]['amount'] * 100) / $item['allowance_charges'][0]['base_amount'], 2)}}</td>
                                @if(isset($item['tax_totals']))
                                    <td class="text-right">{{number_format(($item['line_extension_amount'] + ($item['tax_totals'][0]['tax_amount'])), 2)}}</td>
                                @else
                                    <td class="text-right">{{number_format(($item['line_extension_amount']), 2)}}</td>
                                @endif
                            @else
                                <td class="text-right">{{number_format("0", 2)}}</td>
                                <td class="text-right">{{number_format("0", 2)}}</td>
                                <td class="text-right">{{number_format($item['invoiced_quantity'] * ($item['line_extension_amount'] / $item['invoiced_quantity']), 2)}}</td>
                            @endif
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>


    {{--seccion de immpuestos --}}

            <!-- Tabla para IVA y Retenciones -->
            <table class="tabla-impuestos" style="font-weight: bold;">
                <tr>
                    <!-- Columna de IVA -->
                    <td style="width: 50%; text-align: center;">
                        <strong>IVA</strong><br>
                        @if(isset($request->tax_totals))
                            <?php $TotalImpuestos = 0; ?>
                            @foreach($request->tax_totals as $item)
                                <?php $TotalImpuestos += $item['tax_amount']; ?>
                                @inject('tax', 'App\Tax')
                                <div>{{$tax->findOrFail($item['tax_id'])['name']}} {{number_format($item['percent'], 2)}}%: {{number_format($item['tax_amount'], 2)}}</div>
                            @endforeach
                        @endif
                    </td>

                    <!-- Columna de Retenciones -->
                    <td style="width: 50%; text-align: center;">
                        <strong>Retenciones</strong><br>
                        @if(isset($withHoldingTaxTotal))
                            <?php $TotalRetenciones = 0; ?>
                            @foreach($withHoldingTaxTotal as $item)
                                <?php $TotalRetenciones += $item['tax_amount']; ?>
                                @inject('tax', 'App\Tax')
                                <div>{{$tax->findOrFail($item['tax_id'])['name']}}: {{number_format($item['tax_amount'], 2)}}</div>
                            @endforeach
                        @endif
                    </td>
                </tr>
            </table>

            <!-- Tabla para Totales -->
            <!-- Tabla para Totales, incluyendo la información adicional -->
            <table class="tabla-totales" style="margin-top: 8px; font-weight: bold;">
                <tr>
                    <th>Nro Lineas</th>
                    <td>{{$ItemNro}}</td>
                </tr>
                <tr>
                    <th>Base</th>
                    <td>{{number_format($request->legal_monetary_totals['line_extension_amount'], 2)}}</td>
                </tr>
                <tr>
                    <th>Impuestos</th>
                    <td>{{number_format($TotalImpuestos, 2)}}</td>
                </tr>
                <tr>
                    <th>Retenciones</th>
                    <td>{{number_format($TotalRetenciones, 2)}}</td>
                </tr>
                @if(isset($request->legal_monetary_totals['allowance_total_amount']))
                    <tr>
                        <th>Descuentos</th>
                        <td>{{number_format($request->legal_monetary_totals['allowance_total_amount'], 2)}}</td>
                    </tr>
                @endif
                @if(isset($request->previous_balance) && $request->previous_balance > 0)
                    <tr>
                        <th>Saldo Anterior</th>
                        <td>{{number_format($request->previous_balance, 2)}}</td>
                    </tr>
                @endif
                <!-- Calculo de Total Factura - Descuentos -->
                <tr>
                    <td><b>Total Factura - Descuentos:</b></td>
                    @if(isset($request->tarifaica))
                        @if(isset($request->legal_monetary_totals['allowance_total_amount']))
                            @if(isset($request->previous_balance))
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                            @else
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] - $TotalRetenciones, 2)}}</td>
                            @endif
                        @else
                            @if(isset($request->previous_balance))
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + 0 + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                            @else
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + 0 - $TotalRetenciones, 2)}}</td>
                            @endif
                        @endif
                    @else
                        @if(isset($request->previous_balance))
                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                        @else
                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] - $TotalRetenciones, 2)}}</td>
                        @endif
                    @endif
                </tr>

                <tr>
                    <td><b>Total a Pagar</b></td>
                    @if(isset($request->tarifaica))
                        @if(isset($request->legal_monetary_totals['allowance_total_amount']))
                            @if(isset($request->previous_balance))
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                            @else
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] - $TotalRetenciones, 2)}}</td>
                            @endif
                        @else
                            @if(isset($request->previous_balance))
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + 0 + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                            @else
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + 0 - $TotalRetenciones, 2)}}</td>
                            @endif
                        @endif
                    @else
                        @if(isset($request->previous_balance))
                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                        @else
                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] - $TotalRetenciones, 2)}}</td>
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
<div id="footer" style="font-size: 9px; text-align: center; margin-top: 10px; font-weight: bold;">
    <hr style="margin-bottom: 4px;">
    <p id='mi-texto'>
        Factura No: {{$resolution->prefix}} - {{$request->number}}<br>
        Fecha y Hora de Generación: {{$date}} - {{$time}}<br>
        <strong> CUFE: {{$cufecude}}</strong>
    </p>

    <div style="text-align: center;">
        <img style="width: 70%;" src="{{$imageQr}}">
    </div>

    @isset($request->foot_note)
        <p id='mi-texto-1'>{{$request->foot_note}}</p>
    @endisset

    <h3> GRACIAS POR SU COMPRA</h3>
</div>
</body>
</html>
