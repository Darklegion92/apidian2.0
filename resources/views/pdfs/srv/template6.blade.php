<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>

<body margin-top:50px>
    @if(isset($request->head_note))
    <div class="row">
        <div class="col-sm-12">
            <table class="table table-bordered table-condensed table-striped table-responsive">
                <thead>
                    <tr>
                        <th class="text-center"><p><strong>{{$request->head_note}}<br/>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    @endif
    <table style="font-size: 9px">
        <tr>
            <td class="vertical-align-top" style="width: 45%;">
                <table>
                    <tr>
                        <td style="padding: 0; width: 40%;">Cliente:</td>
                        <td style="padding: 0;">{{$customer->name}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 40%;">CC o NIT:</td>
                        <td style="padding: 0;">{{$customer->company->identification_number}}-{{$request->customer['dv'] ?? NULL}} </td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 40%;">Régimen:</td>
                        <td style="padding: 0;">{{$customer->company->type_regime->name}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 40%;">Obligación:</td>
                        <td style="padding: 0;">{{$customer->company->type_liability->name}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 40%;">Email:</td>
                        <td style="padding: 0;">{{$customer->email}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 40%;">Forma de Pago:</td>
                        <td style="padding: 0;">{{$paymentForm[0]->name}}</td>
                    </tr>
                </table>
            </td>
            <td class="vertical-align-top" style="width: 35%; padding-left: 1rem">
                <table>
                    <tr>
                        <td style="padding: 0; width: 50%;">Dirección:</td>
                        <td style="padding: 0;">{{$customer->company->address}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 50%;">Ciudad:</td>
                        @if($customer->company->country->id == 46)
                            <td style="padding: 0;">{{$customer->company->municipality->name}} - {{$customer->company->country->name}} </td>
                        @else
                            <td style="padding: 0;">{{$customer->company->municipality_name}} - {{$customer->company->state_name}} - {{$customer->company->country->name}} </td>
                        @endif
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 50%;">Teléfono:</td>
                        <td style="padding: 0;">{{$customer->company->phone}}</td>
                    </tr>
                    <br>
                    <tr>
                        <td style="padding: 0; width: 50%;">Medios de Pago:</td>
                        <td style="padding: 0;">
                            @foreach ($paymentForm as $paymentF)
                                {{$paymentF->nameMethod}}<br>
                            @endforeach
                        </td>
                    </tr>
                </table>
            </td>
            <td class="vertical-align-top" style="width: 25%; text-align: right">
                <table>
                    @if(isset($request['order_reference']['id_order']))
                    <tr>
                        <td style="padding: 0; width: 50%;">Número Pedido:</td>
                        <td style="padding: 0;">{{$request['order_reference']['id_order']}}</td>
                    </tr>
                    @endif
                    @if(isset($request['order_reference']['issue_date_order']))
                    <tr>
                        <td style="padding: 0; width: 50%;">Fecha Pedido:</td>
                        <td style="padding: 0;">{{$request['order_reference']['issue_date_order']}}</td>
                    </tr>
                    @endif
                    @if(isset($healthfields))
                    <tr>
                        <td style="padding: 0; width: 50%;">Inicio Periodo Facturación:</td>
                        <td style="padding: 0;">{{$healthfields->invoice_period_start_date}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 50%;">Fin Periodo Facturación:</td>
                        <td style="padding: 0;">{{$healthfields->invoice_period_end_date}}</td>
                    </tr>
                    @endif
                    @if(isset($request['number_account']))
                    <tr>
                        <td style="padding: 0; width: 50%;">Número de cuenta:</td>
                        <td style="padding: 0;">{{$request['number_account'] }}</td>
                    </tr>
                    @endif
                    @if(isset($request['deliveryterms']))
                    <tr>
                        <td style="padding: 0; width: 50%;">Terminos de Entrega:</td>
                        <td style="padding: 0;">{{$request['deliveryterms']['loss_risk_responsibility_code']}} - {{ $request['deliveryterms']['loss_risk'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 50%;">T.R.M:</td>
                        <td style="padding: 0;">{{number_format($request['k_supplement']['FctConvCop'], 2)}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 50%;">Destino</td>
                        <td style="padding: 0;">{{$request['k_supplement']['destination']}}</td>
                    </tr>
                    <tr>
                        @inject('currency', 'App\TypeCurrency')
                        <td style="padding: 0; width: 50%;">Tipo Moneda:</td>
                        <td style="padding: 0;">{{$currency->where('code', 'like', '%'.$request['k_supplement']['MonedaCop'].'%')->firstOrFail()['name']}}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding: 0; width: 50%;">Plazo Para Pagar:</td>
                        <td style="padding: 0;">{{$paymentForm[0]->duration_measure}} Dias</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 50%;">Fecha Vencimiento:</td>
                        <td style="padding: 0;">{{$paymentForm[0]->payment_due_date}}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <br>
    @isset($request['spd'])
        <table class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 100%;">INFORMACION REFERENCIAL DOCUMENTO EQUIVALENTE DE SERVICIOS PUBLICOS Y DOMICILIARIOS</th>
                </tr>
            </thead>
        </table>
        <?php $i = 0; ?>
        @foreach($request['spd'] as $spd)
            <table class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50%;">Informacion Empresa</th>
                        <th class="text-center" style="width: 50%;">Informacion Suscriptor</th>
                    </tr>
                </thead>
                <tbody>
                    @inject('typespd', 'App\TypeSPD')
                    @inject('municipality', 'App\Municipality')
                    @inject('unit_measure', 'App\UnitMeasure')
                    <tr>
                        <td style="padding: 0;">
                            <p style="font-size: 8px">Tipo Servicio     : {{$typespd->where('id', 'like', $request['spd'][$i]['agency_information']['type_spd_id'].'%')->firstOrFail()['name']}}</p>
                            <p style="font-size: 8px">Razon Social      : {{$request['spd'][$i]['agency_information']['office_lending_company']}}</p>
                            <p style="font-size: 8px">Numero de Contrato: {{$request['spd'][$i]['agency_information']['contract_number']}}</p>
                            <p style="font-size: 8px">Periodo Servicio  : {{$request['spd'][$i]['agency_information']['start_period_date']}} - {{$request['spd'][$i]['agency_information']['end_period_date']}}</p>
                            <p style="font-size: 8px">Observaciones     : {{$request['spd'][$i]['agency_information']['note']}}</p>
                        </td>
                        <td style="padding: 0;">
                            <p style="font-size: 8px">Nombre Suscriptor : {{$request['spd'][$i]['subscriber_party']['party_name']}}</p>
                            <p style="font-size: 8px">Direccion 1       : {{$request['spd'][$i]['subscriber_party']['street_name']}}</p>
                            <p style="font-size: 8px">Direccion 2       : {{$request['spd'][$i]['subscriber_party']['additional_street_name']}}</p>
                            <p style="font-size: 8px">Ciudad            : {{$municipality->where('id', 'like', $request['spd'][$i]['subscriber_party']['municipality_id'].'%')->firstOrFail()['name']}}</p>
                            <p style="font-size: 8px">Estrato           : {{$request['spd'][$i]['subscriber_party']['stratum']}}</p>
                            <p style="font-size: 8px">Correo Electronico: {{$request['spd'][$i]['subscriber_party']['email']}}</p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 100%;">INFORMACION REFERENCIAL CONSUMOS</th>
                    </tr>
                </thead>
            </table>
            <table class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 8%;">Duracion del Ciclo</th>
                        <th class="text-center" style="width: 8%;">Unidad de Medida</th>
                        <th class="text-center" style="width: 10%;">Cantidad Medida</th>
                        <th class="text-center" style="width: 12%;">Valor a Pagar Srv</th>
                        <th class="text-center" style="width: 12%;">Consumo Facturado</th>
                        <th class="text-center" style="width: 12%;">Vr. Base Servicio</th>
                        <th class="text-center" style="width: 38%;">Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-right" style="font-size: 8px">{{$request['spd'][$i]['subscriber_consumption']['duration_of_the_billing_cycle']}}</td>
                        <td class="text-right" style="font-size: 8px">{{$unit_measure->where('id', 'like', $request['spd'][$i]['subscriber_consumption']['total_metered_unit_id'].'%')->firstOrFail()['name']}}</td>
                        <td class="text-right" style="font-size: 8px">{{$request['spd'][$i]['subscriber_consumption']['total_metered_quantity']}}</td>
                        <td class="text-right" style="font-size: 8px">{{$request['spd'][$i]['subscriber_consumption']['consumption_payable_amount']}}</td>
                        <td class="text-right" style="font-size: 8px">{{$request['spd'][$i]['subscriber_consumption']['consumption_price_quantity']}}</td>
                        <td class="text-right" style="font-size: 8px">{{$request['spd'][$i]['subscriber_consumption']['partial_line_extension_amount']}}</td>
                        <td class="text-left" style="font-size: 8px">{{$request['spd'][$i]['subscriber_consumption']['consumption_section_note']}}</td>
                    </tr>
                </tbody>
            </table>
            @isset($request['spd'][$i]['subscriber_consumption']['utiliy_meter'])
                <table class="table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 10%;">Serial Medidor</th>
                            <th class="text-center" style="width: 10%;">Fecha Medicion Anterior</th>
                            <th class="text-center" style="width: 10%;">Cantidad Anterior</th>
                            <th class="text-center" style="width: 10%;">Fecha Ultima Medicion</th>
                            <th class="text-center" style="width: 10%;">Cantidad Actual</th>
                            <th class="text-center" style="width: 30%;">Metodo de Lectura</th>
                            <th class="text-center" style="width: 10%;">Duracion Ciclo</th>
                            <th class="text-center" style="width: 10%;">Valor Unitario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-right" style="font-size: 8px">{{$request['spd'][$i]['subscriber_consumption']['utiliy_meter']['meter_number']}}</td>
                            <td class="text-right" style="font-size: 8px">{{$request['spd'][$i]['subscriber_consumption']['utiliy_meter']['previous_meter_reading_date']}}</td>
                            <td class="text-right" style="font-size: 8px">{{$request['spd'][$i]['subscriber_consumption']['utiliy_meter']['previous_meter_quantity']}}</td>
                            <td class="text-right" style="font-size: 8px">{{$request['spd'][$i]['subscriber_consumption']['utiliy_meter']['latest_meter_reading_date']}}</td>
                            <td class="text-right" style="font-size: 8px">{{$request['spd'][$i]['subscriber_consumption']['utiliy_meter']['latest_meter_quantity']}}</td>
                            <td class="text-right" style="font-size: 8px">{{$request['spd'][$i]['subscriber_consumption']['utiliy_meter']['meter_reading_method']}}</td>
                            <td class="text-right" style="font-size: 8px">{{$request['spd'][$i]['subscriber_consumption']['utiliy_meter']['duration_measure']}}</td>
                            <td class="text-left" style="font-size: 8px">{{$request['spd'][$i]['subscriber_consumption']['unstructured_price']['price_amount']}}</td>
                        </tr>
                    </tbody>
                </table>
            @endisset
            <table class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 30%;">Descuentos</th>
                        <th class="text-center" style="width: 30%;">Cargos</th>
                        <th class="text-center" style="width: 40%;">Historico Consumos</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <table class="table" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Concepto</th>
                                        <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($request['spd'][$i]['subscriber_consumption']['descuentos_credito_al_item'])
                                        @foreach($request['spd'][$i]['subscriber_consumption']['descuentos_credito_al_item'] as $descuento)
                                            <tr>
                                                <td>{{$descuento['allowance_reason']}}</td>
                                                <td>{{$descuento['amount']}}</td>
                                            </tr>
                                        @endforeach
                                    @endisset
                                </tbody>
                            </table>
                        </td>
                        <td>
                            <table class="table" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Concepto</th>
                                        <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($request['spd'][$i]['subscriber_consumption']['cargos_debito_al_item'])
                                        @foreach($request['spd'][$i]['subscriber_consumption']['cargos_debito_al_item'] as $cargo)
                                            <tr>
                                                <td>{{$cargo['charge_reason']}}</td>
                                                <td>{{$cargo['amount']}}</td>
                                            </tr>
                                        @endforeach
                                    @endisset
                                </tbody>
                            </table>
                        </td>
                        <td>
                            <table class="table" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Cantidad</th>
                                        <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Fecha Ini</th>
                                        <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Fecha Fin</th>
                                        <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Dias</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($request['spd'][$i]['subscriber_consumption']['consumption_history'] as $consumo)
                                        <tr>
                                            <td>{{$consumo['total_invoiced_quantity']}}</td>
                                            <td>{{$consumo['start_date']}}</td>
                                            <td>{{$consumo['end_date']}}</td>
                                            <td>{{$consumo['duration_measure']}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 100%;">ACUERDOS DE PAGO</th>
                    </tr>
                </thead>
            </table>
            <table class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 8%;">Contrato</th>
                        <th class="text-center" style="width: 8%;">Concepto</th>
                        <th class="text-center" style="width: 15%;">Descripcion</th>
                        <th class="text-center" style="width: 8%;">Cuotas Faltantes</th>
                        <th class="text-center" style="width: 8%;">Cuotas Pagadas</th>
                        <th class="text-center" style="width: 8%;">Tasa de Interes</th>
                        <th class="text-center" style="width: 11%;">Saldo Actual</th>
                        <th class="text-center" style="width: 14%;">Descripcion</th>
                        <th class="text-center" style="width: 8%;">Valor Cuota</th>
                        <th class="text-center" style="width: 6%;">Descuento</th>
                        <th class="text-center" style="width: 6%;">Cargo</th>
                    </tr>
                </thead>
                <tbody>
                    @isset($request['spd'][$i]['subscriber_consumption']['payment_agreements'])
                        @foreach($request['spd'][$i]['subscriber_consumption']['payment_agreements'] as $payment_agreement)
                            <tr>
                                <td>{{$payment_agreement['contract_number']}}</td>
                                <td>{{$payment_agreement['good_service_name']}}</td>
                                <td>{{$payment_agreement['description']}}</td>
                                <td>{{$payment_agreement['fees_to_pay']}}</td>
                                <td>{{$payment_agreement['paid_fees']}}</td>
                                <td>{{$payment_agreement['interest_rate']}}</td>
                                <td>{{$payment_agreement['balance_to_pay']}}</td>
                                <td>{{$payment_agreement['transaction_description']}}</td>
                                <td>{{$payment_agreement['fee_value_to_pay']}}</td>
                                <td>{{$payment_agreement['item_credit_discount']}}</td>
                                <td>{{$payment_agreement['item_debit_charge']}}</td>
                            </tr>
                        @endforeach
                    @endisset
                </tbody>
            </table>
            <?php $i++ ?>
        @endforeach
        <br>
    @endisset
    <table class="table" style="width: 100%;">
        <thead>
            <tr>
                <th class="text-center" style="width: 100%;">DETALLE DEL DOCUMENTO EQUIVALENTE DE SERVICIOS PUBLICOS DOMICILIARIOS</th>
            </tr>
        </thead>
    </table>
    <table class="table" style="width: 100%;">
        <thead>
            <tr>
                <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">#</th>
                <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Código</th>
                <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Descripción</th>
                <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Cantidad</th>
                <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">UM</th>
                <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Val. Unit</th>
                <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">IVA/INC</th>
                <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">IC</th>
                <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Dcto Unit.</th>
                <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">%</th>
                <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Val. Item</th>
            </tr>
        </thead>
        <tbody>
            <?php $ItemNro = 0; $TotalDescuentosEnLineas = 0; ?>
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
                            <?php $TotalDescuentosEnLineas = $TotalDescuentosEnLineas + $item['allowance_charges'][0]['amount'] ?>
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
                                <p style="font-style: italic; font-size: 6px"><strong> {{$item['notes']}}</strong></p>
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

                        @if(isset($item['tax_totals']))
                            @if(isset($item['tax_totals'][1]['tax_amount']))
                                <td class="text-right">{{number_format($item['tax_totals'][1]['tax_amount'] / $item['invoiced_quantity'], 2)}}</td>
                            @else
                                <td class="text-right">{{number_format(0, 2)}}</td>
                            @endif
                        @else
                            <td class="text-right">N/A</td>
                        @endif

                        @if(isset($item['allowance_charges']))
                            <?php $TotalDescuentosEnLineas = $TotalDescuentosEnLineas + ($item['allowance_charges'][0]['amount']) ?>
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

    <br>

    <table class="table" style="width: 100%">
        <thead>
            <tr>
                <th class="text-center" style="border: none;"></th>
                <th class="text-center">Impuestos y Retenciones</th>
                <th class="text-center">Totales</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center" style="width: 30%;">
                    <p style="color: black; font-weight: bold; font-size: 7px; margin-bottom: 2px; padding: 0px 0px 0px 0px;">Resolución de Facturación Electrónica<br>
                                                                                                             Nro. {{$resolution->resolution}} de {{$resolution->resolution_date}}<br>
                                                                                                             Prefijo: {{$resolution->prefix}}, Rango {{$resolution->from}} Al {{$resolution->to}}<br>
                                                                                                             Vigencia Desde: {{$resolution->date_from}} Hasta: {{$resolution->date_to}}</p>
                    <img style="width: 180px;" src="{{$imageQr}}">
                </td>
                <td style="width: 35%;">
                    <table class="table" style="width: 100%">
                        <thead>
                            <tr>
                                <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Tipo</th>
                                <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Base</th>
                                <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Porcentaje</th>
                                <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($request->tax_totals))
                                <?php $TotalImpuestos = 0; ?>
                                @foreach($request->tax_totals as $item)
                                    <tr>
                                        <?php $TotalImpuestos = $TotalImpuestos + $item['tax_amount'] ?>
                                        @inject('tax', 'App\Tax')
                                        <td>{{$tax->findOrFail($item['tax_id'])['name']}}</td>
                                        <td class="text-right">{{number_format($item['taxable_amount'], 2)}}</td>
                                        <td class="text-right">{{number_format($item['percent'], 2)}}%</td>
                                        <td class="text-right">{{number_format($item['tax_amount'], 2)}}</td>
                                    </tr>
                                @endforeach
                            @else
                                <?php $TotalImpuestos = 0; ?>
                            @endif
                            @if(isset($withHoldingTaxTotal))
                                <?php $TotalRetenciones = 0; ?>
                                @foreach($withHoldingTaxTotal as $item)
                                    <tr>
                                        <?php $TotalRetenciones = $TotalRetenciones + $item['tax_amount'] ?>
                                        @inject('tax', 'App\Tax')
                                        <td>{{$tax->findOrFail($item['tax_id'])['name']}}</td>
                                        <td class="text-right">{{number_format($item['taxable_amount'], 2)}}</td>
                                        <td class="text-right">{{number_format($item['percent'], 2)}}%</td>
                                        <td class="text-right">{{number_format($item['tax_amount'], 2)}}</td>
                                    </tr>
                                @endforeach
                            @else
                                <?php $TotalRetenciones = 0; ?>
                            @endif
                        </tbody>
                    </table>
                </td>
                <td style="width: 35%;">
                    <table class="table" style="width: 100%">
                        <thead>
                            <tr>
                                <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Concepto</th>
                                <th class="text-center" style="background-color: rgb(174, 174, 186); color: black;">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Nro Lineas:</td>
                                <td class="text-right">{{$ItemNro}}</td>
                            </tr>
                            <tr>
                                <td>Base:</td>
                                <td class="text-right">{{number_format($request->legal_monetary_totals['line_extension_amount'], 2)}}</td>
                            </tr>
                            <tr>
                                <td>Impuestos:</td>
                                <td class="text-right">{{number_format($TotalImpuestos, 2)}}</td>
                            </tr>
                            <tr>
                                <td>Retenciones:</td>
                                <td class="text-right">{{number_format($TotalRetenciones, 2)}}</td>
                            </tr>
                            <tr>
                                <td>Descuentos En Lineas:</td>
                                <td class="text-right">{{number_format($TotalDescuentosEnLineas, 2)}}</td>
                            </tr>
                            <tr>
                                <td>Descuentos Globales:</td>
                                @if(isset($request->legal_monetary_totals['allowance_total_amount']))
                                    <td class="text-right">{{number_format($request->legal_monetary_totals['allowance_total_amount'], 2)}}</td>
                                @else
                                    <td class="text-right">{{number_format(0, 2)}}</td>
                                @endif
                            </tr>

                            @if(isset($request->legal_monetary_totals['charge_total_amount']))
                                @if($request->legal_monetary_totals['charge_total_amount'] > 0)
                                    <?php $charge_number = 0; ?>
                                    @foreach($request['allowance_charges'] as $allowance_charge)
                                        @if(isset($allowance_charge))
                                            @if($allowance_charge['charge_indicator'] == true)
                                                <?php $charge_number++; ?>
                                                <tr>
                                                    <td>{{$allowance_charge['allowance_charge_reason'] ?? "Cargo Global Nro: ".$charge_number}}</td>
                                                    <td class="text-right">{{number_format($allowance_charge['amount'], 2)}}</td>
                                                </tr>
                                            @endif
                                        @endif
                                    @endforeach
                                @endif
                            @endif


                            @if(isset($request->previous_balance))
                                @if($request->previous_balance > 0)
                                    <tr>
                                        <td>Saldo Anterior:</td>
                                        <td class="text-right">{{number_format($request->previous_balance, 2)}}</td>
                                    </tr>
                                @endif
                            @endif
                            <tr>
                                <td>Total Factura - Descuentos:</td>
                                @if(isset($request->tarifaica))
                                    @if(isset($request->legal_monetary_totals['allowance_total_amount']))
                                        @if(isset($request->previous_balance))
                                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance, 2)}}</td>
                                        @else
                                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'], 2)}}</td>
                                        @endif
                                    @else
                                        @if(isset($request->previous_balance))
                                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + 0 + $request->previous_balance, 2)}}</td>
                                        @else
                                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + 0, 2)}}</td>
                                        @endif
                                    @endif
                                @else
                                    @if(isset($request->previous_balance))
                                        <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance, 2)}}</td>
                                    @else
                                        <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'], 2)}}</td>
                                    @endif
                                @endif
                            </tr>

                            <tr>
                                <td>Total a Pagar</td>
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
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    @inject('Varios', 'App\Custom\NumberSpellOut')
    <div class="text-right" style="margin-top: -25px;">
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
            </p>
        </div>
    </div>
    <p><strong>PRECIO EN LETRAS SON</strong>: {{$Varios->convertir($totalAmount, $idcurrency)}} M/CTE*********.</p>

    @if(isset($notes))
        <div class="summarys">
            <div class="text-word" id="note">
                <p><strong>OBSERVACIONES:</strong></p>
                <p style="font-style: italic; font-size: 9px">{{$notes}}</p>
            </div>
        </div>
    @endif

    <div class="summary" >
        <div class="text-word" id="note">
            @if(isset($request->disable_confirmation_text))
                @if(!$request->disable_confirmation_text)
                    <p style="font-style: italic;">INFORME EL PAGO AL TELEFONO {{$company->phone}} o al e-mail {{$user->email}}<br>
                        <br>
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
</body>
</html>
