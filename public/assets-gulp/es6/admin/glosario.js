
function editarModalIndustria(id, nombre){
    //alert("hola mundowww");
    console.log(id);
    console.log(nombre);

    $("#nombre_i").val(nombre);
    $("#id_i").val(id);

    $("#modal_industria").modal('show');
}

function addCompanias(id, nombre){
    //alert("hola industrias");
    console.log(id);
    console.log(nombre);

    if (id>0) {
      
        var _id = id;

        var a = new FormData();
        a.append('id_industria', id );

        fetch("/admin/getcompanias", {
            method: "POST",
            body: a
        }).then(function(e) {
            return e.json();
        }).then(function(e) {
            //alert("get compañias");
            
            if(e.success){

                let data = e.data;
                console.log(data);
                   
                let myTable= "<table class='table align-middle' id='companiasTable' style='width: 100%;text-align: center;'>";
                myTable+= "<thead>";           

                    myTable+= "<tr>";   
                        myTable+= "<th  class='align-middle' style='width: 10%;'>";
                           myTable+= "<div id=''> Id </div>";
                        myTable+= "</th>";
                        myTable+= "<th  class='align-middle' style='width: 85%;'>";
                          myTable+= "<div id=''> Nombre </div>";
                        myTable+= "</th>";
                        myTable+= "<th class='align-middle' style='width: 5%;'>";
                          myTable+= "<div id=''> Acción </div>";
                        myTable+= "</th>";

                    myTable+= "</tr>";

                myTable+= "</thead>";
                        myTable+= "<tbody>";

                        for (let i = 0; i < data.length; i++) {
                           
                            myTable+= "<tr>";

                                myTable+= "<th  style=''>";
                                    myTable+= "<div id=''> "+ data[i].id +" </div>";
                                myTable+= "</th>";  

                                myTable+= "<th  style='text-align: left;'>";
                                    myTable+= "<div id=''> "+ data[i].nombre +" </div>";
                                myTable+= "</th>";  

                                myTable+= "<th  style=''>";                                  
  
                                       myTable+= "<button type='button' class='btn btn-outline-secondary float-right' onclick='deleteCompania("+data[i].id+","+_id+")'> <i class='fa fa-trash' aria-hidden='true'></i></button> ";
                                                                 
                                myTable+= "</th>";

                        myTable+= "</tr>";  
                            
                        }
                                                 

                myTable+= "</tbody>";
                myTable+= "</table>";
                
                
                $("#id_in").val(_id);

                $("#tabla_companias").html(myTable);
                $("#modal_companias").modal('show');
                
            }

           /* e.success && (Confirm.fire("¡Elimnado!", "El registro fue eliminado.", "success"), 
            setTimeout(function() {
                location.reload();
            }, 1e3));*/
        }).catch(function() {
            alert("Hubo un error en el sistema.");
        });
        
    } else{
        Toast.fire({
            icon: "info",
            title: "No se tiene un id de industria"
        });
    };



    //$("#modal_industria").modal('show');
}

function deleteCompania(e, ind) {

    var industria_id = ind;

    Confirm.fire({
        title: "¿Estas seguro que desea eliminar el registro?",
        text: "¡Se eliminará el registro permanetemente!",
        icon: "warning",
        showCancelButton: !0,
        cancelButtonText: "No",
        reverseButtons: !1
    }).then(function(t) {
        if (t.isConfirmed) {
            //alert(t.isConfirmed);
            var a = new FormData();
            a.append("id_industria", industria_id)
            a.append("id_compania", e), fetch("/admin/compania-delete", {
                method: "POST",
                body: a
            }).then(function(e) {
                return e.json();
            }).then(function(e) {
                console.log(e);
                if(e.success){                
                    
                 
                    console.log(industria_id);
                    let data2 = e.data;
                    console.log("----");
                    console.log(data2);                 
                    
                       
                    let myTable2= "<table class='table align-middle' id='companiasTable' style='width: 100%;text-align: center;'>";
                    myTable2+= "<thead>";           
    
                        myTable2+= "<tr>";   
                            myTable2+= "<th  class='align-middle' style='width: 10%;'>";
                               myTable2+= "<div id=''> Id </div>";
                            myTable2+= "</th>";
                            myTable2+= "<th  class='align-middle' style='width: 85%;'>";
                              myTable2+= "<div id=''> Nombre </div>";
                            myTable2+= "</th>";
                            myTable2+= "<th class='align-middle' style='width: 5%;'>";
                              myTable2+= "<div id=''> Acción </div>";
                            myTable2+= "</th>";
    
                        myTable2+= "</tr>";
    
                    myTable2+= "</thead>";
                            myTable2+= "<tbody>";
    
                            for (let c = 0; c < data2.length; c++) {
                           
                                myTable2+= "<tr>";
    
                                    myTable2+= "<td>";
                                        myTable2+= "<div>"+ data2[c].id +"</div>";
                                    myTable2+= "</td>"; 

                                    myTable2+= "<td style='text-align: left;'>";
                                       myTable2+= "<div>"+ data2[c].nombre +"</div>";
                                    myTable2+= "</td>"; 

                                    myTable2+= "<td>";                                 
  
                                       myTable2+= "<button type='button' class='btn btn-outline-secondary float-right' onclick='deleteCompania("+data2[c].id+","+industria_id+")'> <i class='fa fa-trash' aria-hidden='true'></i></button>";
                                                              
                                    myTable2+= "</td>";
    
                                myTable2+= "</tr>";  
                                
                            }                                                     
    
                    myTable2+= "</tbody>";
                    myTable2+= "</table>";                    
    
                    $("#tabla_companias").html(myTable2);

                    Confirm.fire("¡Elimnado!", "El registro fue eliminado.", "success")

                }                   
             

                /*setTimeout(function() {
                    location.reload();
                }, 1e3));*/


            }).catch(function() {
                alert("Hubo un error en el sistema.");
            });

        } else Confirm.fire("Cancelado", "El registro está a salvo", "error");
    });
}

function closeModalCompanias(){
    $("#modal_companias").modal('hide');
    window.location.href = window.location.href;  
   
}

function deleteIndustria(ind) {

    var industria_id = ind;

    Confirm.fire({
        title: "¿Estas seguro que desea eliminar el registro?",
        text: "¡Se eliminará el registro permanetemente!",
        icon: "warning",
        showCancelButton: !0,
        cancelButtonText: "No",
        reverseButtons: !1
    }).then(function(t) {
        if (t.isConfirmed) {
            //alert(t.isConfirmed);
            var a = new FormData();
            a.append("id_industria", industria_id)
            fetch("/admin/industria-delete", {
                method: "POST",
                body: a
            }).then(function(e) {
                return e.json();
            }).then(function(e) {
                console.log(e);
                if(e.success){       

                    Confirm.fire("¡Elimnado!", "El registro fue eliminado.", "success")
                    window.location.href = window.location.href;

                }                   
             

                /*setTimeout(function() {
                    location.reload();
                }, 1e3));*/


            }).catch(function() {
                alert("Hubo un error en el sistema.");
            });

        } else Confirm.fire("Cancelado", "El registro está a salvo", "error");
    });
}

var frmIndustria = document.querySelector("#frmIndustria");

frmIndustria && frmIndustria.addEventListener("submit", function(e) {
    //alert("hola");
    
    e.preventDefault();
    var t = new FormData(e.currentTarget), a = "/admin/industria-update";
    //$("#id_servicio").val() || (a = "/admin/servicio-add"), 
    
    let _nombre_industria = $("#nombre_i").val();

    if(_nombre_industria){
        fetch(a, {
            method: "POST",
            body: t
        }).then(function(e) {
            return e.json();
        }).then(function(e) {
            e.success && (
                            $("#frmIndustria")[0].reset(), 
                                Toast.fire({
                                    icon: "success",
                                    title: "Registro exitoso."
                                }), setTimeout(function() {
                                    //location.reload();
                                    window.location.href = window.location.href;
                                }, 1e3)
                        );

        }).catch(function() {
            alert("Hubo un error en el sistema.");
        });
    }else{
        Toast.fire({
            icon: "info",
            title: "Debe ingresar el nombre de la industria."
        })
    }

           
});

var frmCompania = document.querySelector("#frmCompania");

frmCompania && frmCompania.addEventListener("submit", function(e) {
   // alert("hola");
    
    e.preventDefault();
    var t = new FormData(e.currentTarget), a = "/admin/compania-update";
    //$("#id_servicio").val() || (a = "/admin/servicio-add"), 
    
    let _nombre_compania = $("#nombre_c").val();
    let industria_id = $("#id_in").val();


    if(_nombre_compania){
        fetch(a, {
            method: "POST",
            body: t
        }).then(function(e) {
            return e.json();
        }).then(function(e) {

            if(e.success){

                console.log(e.data);

                let data2 = e.data;

                let myTable2= "<table class='table align-middle' id='companiasTable' style='width: 100%;text-align: center;'>";
                myTable2+= "<thead>";           

                    myTable2+= "<tr>";   
                        myTable2+= "<th  class='align-middle' style='width: 10%;'>";
                           myTable2+= "<div id=''> Id </div>";
                        myTable2+= "</th>";
                        myTable2+= "<th  class='align-middle' style='width: 85%;' >";
                          myTable2+= "<div id=''> Nombre </div>";
                        myTable2+= "</th>";
                        myTable2+= "<th class='align-middle' style='width: 10%;'>";
                          myTable2+= "<div id=''> Acción </div>";
                        myTable2+= "</th>";

                    myTable2+= "</tr>";

                myTable2+= "</thead>";
                        myTable2+= "<tbody>";

                        for (let c = 0; c < data2.length; c++) {
                       
                            myTable2+= "<tr>";

                                myTable2+= "<td>";
                                    myTable2+= "<div>"+ data2[c].id +"</div>";
                                myTable2+= "</td>"; 

                                myTable2+= "<td style='text-align: left;'>";
                                   myTable2+= "<div>"+ data2[c].nombre +"</div>";
                                myTable2+= "</td>"; 

                                myTable2+= "<td>";                                 

                                   myTable2+= "<button type='button' class='btn btn-outline-secondary float-right' onclick='deleteCompania("+data2[c].id+","+industria_id+")'> <i class='fa fa-trash' aria-hidden='true'></i></button>";
                                                          
                                myTable2+= "</td>";

                            myTable2+= "</tr>";  
                            
                        }                                                     

                myTable2+= "</tbody>";
                myTable2+= "</table>";                    

                $("#tabla_companias").html(myTable2);

                Confirm.fire("¡Registro Exitoso!", "El registro fue registrado correctamente.", "success")

                $("#nombre_c").val("");

            }

            /*$("#frmCompania")[0].reset(), 
                Toast.fire({
                    icon: "success",
                    title: "Registro exitoso."
                }),*/
                                
           /* setTimeout(function() {
                //location.reload();
                window.location.href = window.location.href;
            }, 1e3)*/
                        

        }).catch(function() {
            alert("Hubo un error en el sistema.");
        });
    }else{
        Toast.fire({
            icon: "info",
            title: "Debe ingresar el nombre de la compañia."
        })
    }
           
});

