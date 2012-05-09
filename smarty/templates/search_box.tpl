<!-- search box -->
<script type="text/javascript">
   function rewrite_form(e) {    
      var form = document.forms[0];   // .getElementById("form1");
      window.location = '/search/' + form.search.value;
      if (e && e.preventDefault) { e.preventDefault(); }
      return false;
   }
</script>

<form action="/search" onsubmit="rewrite_form(event)" method="GET">
  <div id="search-wrapper" class="ui-body ui-body-c ui-corner-all" >
    <input type="search" name="search" id="search" value="" data-inline="true"/>
    <input type="submit" value="{t}find{/t}" data-icon="search" data-inline="true" data-mini="true"/>
  </div>
</form>
