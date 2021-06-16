<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Stock Manager</title>
    <script>
    var base_url = "{{ url('') }}";
    </script>
    <link href="{{ asset("css/bootstrap.min.css") }}" rel="stylesheet"/>
    <style>
    #stockList {display: none;}
    </style>
    </head>
    <body>
      <nav aria-label="breadcrumb">
        <div class="container">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">Stock Manager</li>
        </ol>
      </div>
      </nav>

      <div class="container">
      	<form method="POST" id="addItemForm"> @csrf
      		<div class="row">
      			<div class="col-md-3">
      				<div class="form-group">
      					<input type="text" class="form-control" autocomplete="off" required name="name" placeholder="Product Name"> </div>
      			</div>
      			<div class="col-md-3">
      				<div class="form-group">
      					<input type="number" min="0" class="form-control" autocomplete="off" required name="quantity" placeholder="Quantity"> </div>
      			</div>
      			<div class="col-md-3">
      				<div class="form-group">
      					<input type="number"  min="0" class="form-control" autocomplete="off" required name="price" placeholder="Price"> </div>
      			</div>
      			<div class="col-md-3">
      				<button type="submit" class="btn btn-primary">Add Stock</button>
      				<button type="reset" onclick="return confirm('Are you sure you want to reset this form?');" class="btn btn-danger">Reset</button>
      			</div>
      		</div>
      	</form>

        <hr/>

  <table class="table table-dark" id="stockList">
    <caption style="caption-side:top;"><h4>Current Stock</h4></caption>
  <thead>
    <tr align="center">
      <th scope="col">#</th>
      <th scope="col">Product name</th>
      <th scope="col">Quantity in stock</th>
      <th scope="col">Price per item</th>
      <th scope="col">Datetime submitted</th>
      <th scope="col">Total value number</th>
      <th scope="col">Actions</th>
    </tr>
  </thead>
  <tbody></tbody>
  <tfoot>
    <tr scope="row" align="center">
      <td colspan="5"></td>
      <td id="total_stock"></td>
      <td>
      <button id="emptyStock" type="button" class="btn btn-danger">Empty All Stock</button>
      </td>

    </tr>
  </tfoot>
  </table>

  </div>


<!-- Modal -->
<div class="modal fade" id="editStockModal" tabindex="-1" role="dialog" aria-labelledby="stockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
          <form role="form" id="editStockForm">
              @csrf
              <input type="hidden" id="path" name="path">
              <!-- Modal Header -->
              <div class="modal-header">
              <h4 class="modal-title" id="stockModalLabel">
                    Edit Stock
              </h4>
              <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
              </button>
              </div>

              <!-- Modal Body -->
              <div class="modal-body">

              <div class="form-group">
                  <label for="">Product Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Product Name" required/>
              </div>
              <div class="form-group">
                  <label for="">Quantity</label>
                    <input type="number" min="0" class="form-control" id="quantity" name="quantity" placeholder="Quantity" required/>
                  </div>
                  <div class="form-group">
                    <label for="">Price</label>
                    <input type="number" min="0" class="form-control"
                    id="price" name="price" placeholder="Price" required/>
                    </div>
              </div>

                  <!-- Modal Footer -->
                  <div class="modal-footer">
                      <button type="button" class="btn btn-default"
                              data-dismiss="modal">
                                  Close
                      </button>
                      <button type="submit" class="btn btn-primary">
                          Save changes
                      </button>
                  </div>
                </form>
              </div>
          </div>
      </div>


        <script src="{{ asset("js/jquery-3.3.1.min.js") }}"></script>
        <script src="{{ asset("js/popper.min.js") }}"></script>
        <script src="{{ asset("js/bootstrap.min.js") }}"></script>
        <script>
        $(function() {
          function fetchAllData() {
            $.ajax({
                url: "{{ route('fetch-stock-items') }}",
                cache: false,
                dataType: 'json',
                success: function(data) {
                  renderStockData(data);
                }
            });
          }

          function renderStockData(data) {
            if(data.rows.length==0) {
              $('#stockList').hide();
              return;
            }

            $('#stockList').show();
            $('#stockList>tbody').html('');

            var rows = data.rows;

            for (var key in rows) {
                $('#stockList>tbody').append('<tr align="center" scope="row"><td>' + rows[key].pos + '</td><td>' + rows[key].name +  '</td><td>' + rows[key].quantity +  '</td><td>' + rows[key].price +  '</td><td>' + rows[key].datetime +  '</td><td>' + rows[key].total +  '</td><td><button data-path="' + rows[key].path + '" class="btn btn-info editStock">Edit</button> &nbsp;&nbsp; <button data-path="' + rows[key].path + '" class="btn btn-danger deleteStock">Delete</button> </td></tr>');
            }

            $('#total_stock').html(data.total);
          }

          //add stock
          $('#addItemForm').on('submit',function() {
            var form = this;
            $.ajax({
                  url: "{{ route('add-stock-item') }}",
                  type: 'post',
                  dataType: 'json',
                  cache: false,
                  data: $(this).serialize(),
                  success: function(data) {
                    $(form).trigger("reset");
                    fetchAllData();
                  }
              });
            return false;
          });

          //edit stock
          $('#editStockForm').on('submit',function() {
            var form = this;
            $.ajax({
                  url: "{{ route('edit-stock-item') }}",
                  type: 'put',
                  dataType: 'json',
                  cache: false,
                  data: $(this).serialize(),
                  success: function(data) {
                    $('#editStockModal').modal('hide');
                    fetchAllData();
                  }
              });
            return false;
          });

          //bind buttons
          $('#stockList').on('click', '.editStock', function(){
            var url=base_url+"/stock/"+$(this).data('path');
            $.ajax({
                url: url,
                cache: false,
                dataType: 'json',
                success: function(response) {
                  $('#editStockForm').find('#path').val(response.data.path);
                  $('#editStockForm').find('#name').val(response.data.name);
                  $('#editStockForm').find('#quantity').val(response.data.quantity);
                  $('#editStockForm').find('#price').val(response.data.price);

                  $('#editStockModal').modal('show');
                },
                error: function () {
                  alert("Unable to find store entry.")
                }
            });
          });


          fetchAllData();
        });
      </script>
    </body>
</html>
