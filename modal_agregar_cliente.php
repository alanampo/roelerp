<div id="ModalAgregarCliente" class="modal" data-keyboard="false" data-backdrop="static">
    <div class="modal-content2">
        <div class='box box-primary'>
            <div class='box-header with-border'>
                <h3 id='titulo' class='box-title'>Agregar Cliente</h3>
            </div>
            <div class='box-body'>
                <div class='form-group'>
                    <div>
                        <label class="control-label">Nombre:</label>
                    </div>
                    <div>
                        <form action="#" onsubmit="$('#domiciliocliente_txt').focus();return false;">
                            <input type="search" autocomplete="off" id="nombrecliente_txt"
                                style="text-transform:uppercase" maxlength="40" class="form-control">
                        </form>
                    </div>
                </div>
                <div class='form-group'>
                    <div>
                        <label class="control-label">Domicilio:</label>
                    </div>
                    <div>
                        <form action="#" onsubmit="$('#domiciliocliente2_txt').focus();return false;">
                            <input type="search" maxlength="100" autocomplete="off" id="domiciliocliente_txt"
                                style="text-transform:uppercase" class="form-control">
                        </form>
                    </div>
                </div>
                <div class='form-group'>
                    <div>
                        <label class="control-label">Domicilio de Envío: <button
                                onClick="$('#domiciliocliente2_txt').val($('#domiciliocliente_txt').val());"
                                type="button" class="btn btn-sm btn-info">=</button></label>
                    </div>
                    <div>
                        <form action="#" onsubmit="$('#select-comuna2').focus();return false;">
                            <input type="search" maxlength="100" autocomplete="off" id="domiciliocliente2_txt"
                                style="text-transform:uppercase" class="form-control">
                        </form>
                    </div>
                </div>
                <div class='form-group'>
                    <div>
                        <label class="control-label">Comuna:</label>
                    </div>
                    <div>
                        <form action="#" onsubmit="$('#telcliente_txt').focus();return false;">
                            <select id="select-comuna2" class="selectpicker" title="Comuna" data-style="btn-info"
                                data-dropup-auto="false" data-live-search="true" data-width="100%"
                                data-size="5"></select>
                        </form>
                    </div>
                </div>
                <div class='form-group'>
                    <div>
                        <label class="control-label">Provincia:</label>
                    </div>
                    <div>
                        <select id="provinciacliente_txt" class="form-control">
                            <option value="ANTÁRTICA CHILENA">ANTÁRTICA CHILENA</option>
                            <option value="ANTOFAGASTA">ANTOFAGASTA</option>
                            <option value="ARAUCO">ARAUCO</option>
                            <option value="ARICA">ARICA</option>
                            <option value="AYSÉN">AYSÉN</option>
                            <option value="BIOBÍO">BIOBÍO</option>
                            <option value="CACHAPOAL">CACHAPOAL</option>
                            <option value="CAPITÁN PRAT">CAPITÁN PRAT</option>
                            <option value="CARDENAL CARO">CARDENAL CARO</option>
                            <option value="CAUTÍN">CAUTÍN</option>
                            <option value="CHAÑARAL">CHAÑARAL</option>
                            <option value="CHACABUCO">CHACABUCO</option>
                            <option value="CHILOÉ">CHILOÉ</option>
                            <option value="CHOAPA">CHOAPA</option>
                            <option value="COIHAYQUE">COIHAYQUE</option>
                            <option value="COLCHAGUA">COLCHAGUA</option>
                            <option value="CONCEPCIÓN">CONCEPCIÓN</option>
                            <option value="CORDILLERA">CORDILLERA</option>
                            <option value="COPIAPÓ">COPIAPÓ</option>
                            <option value="CURICÓ">CURICÓ</option>
                            <option value="DIGUILLÍN">DIGUILLÍN</option>
                            <option value="EL LOA">EL LOA</option>
                            <option value="ELQUI">ELQUI</option>
                            <option value="GENERAL CARRERA">GENERAL CARRERA</option>
                            <option value="HUASCO">HUASCO</option>
                            <option value="IQUIQUE">IQUIQUE</option>
                            <option value="ISLA DE PASCUA">ISLA DE PASCUA</option>
                            <option value="ITATA">ITATA</option>
                            <option value="LAJA">LAJA</option>
                            <option value="LINARES">LINARES</option>
                            <option value="LLANQUIHUE">LLANQUIHUE</option>
                            <option value="LOS ANDES">LOS ANDES</option>
                            <option value="LOS LAGOS">LOS LAGOS</option>
                            <option value="MALLECO">MALLECO</option>
                            <option value="MAIPO">MAIPO</option>
                            <option value="MARGA MARGA">MARGA MARGA</option>
                            <option value="MELIPILLA">MELIPILLA</option>
                            <option value="OSORNO">OSORNO</option>
                            <option value="PALENA">PALENA</option>
                            <option value="PARINACOTA">PARINACOTA</option>
                            <option value="PETORCA">PETORCA</option>
                            <option value="PUNILLA">PUNILLA</option>
                            <option value="QUILLOTA">QUILLOTA</option>
                            <option value="RANCO">RANCO</option>
                            <option value="SAN ANTONIO">SAN ANTONIO</option>
                            <option value="SAN FELIPE DE ACONCAGUA">SAN FELIPE DE ACONCAGUA</option>
                            <option value="SANTIAGO">SANTIAGO</option>
                            <option value="TALAGANTE">TALAGANTE</option>
                            <option value="TALCA">TALCA</option>
                            <option value="TAMARUGAL">TAMARUGAL</option>
                            <option value="TIERRA DEL FUEGO">TIERRA DEL FUEGO</option>
                            <option value="TOCOPILLA">TOCOPILLA</option>
                            <option value="ULTIMA ESPERANZA">ULTIMA ESPERANZA</option>
                            <option value="VALDIVIA">VALDIVIA</option>
                            <option value="VALPARAÍSO">VALPARAÍSO</option>
                        </select>
                    </div>
                </div>

                <div class='form-group'>
                    <div>
                        <label class="control-label">Región:</label>
                    </div>
                    <div>
                        <select id="regioncliente_txt" class="form-control">
                            <option value="ANTOFAGASTA">ANTOFAGASTA</option>
                            <option value="ARICA Y PARINACOTA">ARICA Y PARINACOTA</option>
                            <option value="ATACAMA">ATACAMA</option>
                            <option value="AYSÉN">AYSÉN</option>
                            <option value="BIOBÍO">BIOBÍO</option>
                            <option value="COQUIMBO">COQUIMBO</option>
                            <option value="LA ARAUCANÍA">LA ARAUCANÍA</option>
                            <option value="LOS LAGOS">LOS LAGOS</option>
                            <option value="LOS RÍOS">LOS RÍOS</option>
                            <option value="MAGALLANES Y ANTÁRTICA CHILENA">MAGALLANES Y ANTÁRTICA CHILENA</option>
                            <option value="MAULE">MAULE</option>
                            <option value="METROPOLITANA DE SANTIAGO">METROPOLITANA DE SANTIAGO</option>
                            <option value="ÑUBLE">ÑUBLE</option>
                            <option value="O`HIGGINS">O`HIGGINS</option>
                            <option value="TARAPACÁ">TARAPACÁ</option>
                            <option value="VALPARAÍSO">VALPARAÍSO</option>
                        </select>
                    </div>
                </div>

                <div class='form-group'>
                    <div>
                        <label class="control-label">Teléfono:</label>
                    </div>
                    <div>
                        <form action="#" onsubmit="$('#rutcliente_txt').focus();return false;">
                            <input type="search" maxlength="30" autocomplete="off" id="telcliente_txt"
                                style="text-transform:uppercase" class="form-control">
                        </form>
                    </div>
                </div>
                <div class='form-group'>
                    <div>
                        <label class="control-label">R.U.T:</label>
                    </div>
                    <div>
                        <form action="#" onsubmit="$('#razonsocial_txt').focus();return false;">
                            <input type="search" maxlength="25" autocomplete="off" id="rutcliente_txt"
                                style="text-transform:uppercase" class="form-control" onpaste="return false">
                        </form>
                    </div>
                </div>
                <div class='form-group'>
                    <div>
                        <label class="control-label">Razón Social: <button class="btn btn-sm btn-secondary"
                                onclick="setRazonSocial()">=</button></label>
                    </div>
                    <div>
                        <form action="#" onsubmit="$('#mailcliente_txt').focus();return false;">
                            <input type="search" maxlength="80" autocomplete="off" id="razonsocial_txt"
                                style="text-transform:uppercase" class="form-control" onpaste="return false">
                        </form>
                    </div>
                </div>
                <div class='form-group'>
                    <div>
                        <label class="control-label">E-Mail:</label>
                    </div>
                    <div>
                        <form action="#" onsubmit="$('#select-vendedor').focus();return false;">
                            <input maxlength="50" type="search" autocomplete="off" id="mailcliente_txt"
                                style="text-transform:lowercase !important" class="form-control">
                        </form>
                    </div>
                </div>
                <div class='form-group' id='grupo-vendedor-agregar'>
                    <div>
                        <label class="control-label">Vendedor Asignado:</label>
                    </div>
                    <div>
                        <form action="#" onsubmit="return false;">
                            <select id="select-vendedor" class="selectpicker" title="Seleccionar Vendedor" data-style="btn-info"
                                data-dropup-auto="false" data-live-search="true" data-width="100%"
                                data-size="5"></select>
                        </form>
                    </div>
                </div>

            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn fa fa-close" style="font-size: 2em"
                    onClick="$('#ModalAgregarCliente').modal('hide');"></button>
                <button type="button" class="btn fa fa-save ml-3" style="font-size: 2em" id="btn_guardarcliente"
                    onClick="GuardarCliente();"></button>
            </div>
        </div>
    </div>
</div> <!-- TERMINA MODAL CLIENTE-->