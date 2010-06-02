<html>
<head>
<script type="text/javascript" src="/js/dygraph-combined.js"></script>
</head>
<body>
<div id="graphdiv3" style="width:95%; height:350px;"></div>
<script type="text/javascript">
  g3 = new Dygraph(
    document.getElementById("graphdiv3"),
    "/js/temperatures.csv",
    {
      rollPeriod: 7,
      showRoller: false
    }
  );
</script>
</body>
</html>