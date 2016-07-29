<!-- /resources/views/terms/show.blade.php -->
@extends('layouts.master')

@section('content')

	<head>
	<script src="{{ URL::asset('js/d3.v4.1.0.min.js') }}"></script>
	<link rel="stylesheet" href="{{ URL::asset('css/font-awesome.min.css') }}">
	</head>

	<style>
      
	.node-rect {
		fill: white;
		stroke: black;
		stroke-width: 1.5;
		cursor: move;
	}
	
	.node-rect.selected {
		fill: #FFC1FF;
	}

	.node-text {
		font-size: 2em;
		text-anchor: middle;
		alignment-baseline: middle;
		pointer-events: none;
		/* Disable text selection
		   from http://stackoverflow.com/questions/826782/css-rule-to-disable-text-selection-highlighting */
		-webkit-touch-callout: none; /* iOS Safari */
		-webkit-user-select: none;   /* Chrome/Safari/Opera */
		-khtml-user-select: none;    /* Konqueror */
		-moz-user-select: none;      /* Firefox */
		-ms-user-select: none;       /* Internet Explorer/Edge */
		user-select: none;
	}

	.link-line {
		stroke: black;
		stroke-width: 1.5;
	}

	/* Set the arrowhead size. */
	.arrow {
		stroke-width: 1.5px;
	}

	svg.main {
		height: 0px;
		width: 0px;
	}

	svg#d3js {
		background-color: #f9f9f9;
		border-radius: 5px;
		border: 1px solid #d3d3d3;
	}
	</style>

	<ul class="breadcrumb breadcrumb-section">
	 <li><a href="{!! url('/'); !!}">Home</a></li>
	 <li><a href="{!! url('/terms'); !!}">Terms</a></li>
	 <li class="active">{{ $term->term_name }}</li>
	</ul>

	<div id="term" class="row">
		<div class="col-xs-6">
			<dl class="dl-horizontal">
			<dt>Term name:</dt>
			<dd id="term_name">{{ $term->term_name }}</dd>
			</dl>

			<dl class="dl-horizontal">
			<dt>Glossary name:</dt>
			<dd id="term_glossay_name">{{ $term->glossary->glossary_name }}</dd>
			</dl>
		</div>
		<div class="col-xs-6">
			<dl class="dl-horizontal">
			<dt>Term status:</dt>
			<dd id="term_status">{{ $term->status->status_name }}</dd>
			</dl>

			<dl class="dl-horizontal">
			<dt>Term owner:</dt>
			<dd id="term_owner">{{ $term->owner->firstname }} {{ $term->owner->lastname }}</dd>
			</dl>
		</div>
	  
		<div class="col-xs-12">
			<dl class="dl-horizontal">
			<dt>Term definition:</dt>
			<dd id="term_description">{!! App\Helper::contentAdjust($term->term_description) !!}</dd>
			</dl>
		</div>
	</div> 

	<div id="spinner" style="text-align: center; margin-top:100px;">
	<p>Loading...</p>
	<i class="fa fa-spinner fa-spin" style="font-size:24px"></i>
	<div>
	<svg class="main"></svg>

	<script>

	$( document ).ready(function() {

		// Define the dimensions of the visualization. We're using
		// a size that's convenient for displaying the graphic
		var width = 960,
			height = 450,
			nodeSize = 20,
			arrowWidth = 8;

		$.getJSON('{{ URL::to('/') }}/api/terms/{{ $term->id }}', function(graph) {
			
			//remove spinner when the load is completed
			$("#spinner").hide();

			svg = d3.select("div.container")
				.append("svg")
				.attr("width", width)
				.attr("height", height)
				.attr("id", 'd3js')
			linkG = svg.append("g")
			nodeG = svg.append("g")
			// Arrows are separate from link lines so that their size
			// can be controlled independently from the link lines.
			arrowG = svg.append("g");
      
			// Arrowhead setup.
			// Draws from Mobile Patent Suits example:
			// http://bl.ocks.org/mbostock/1153292
			svg.append("defs")
				.append("marker")
				.attr("id", "arrow")
				.attr("orient", "auto")
				.attr("preserveAspectRatio", "none")
				// See also http://www.w3.org/TR/SVG/coords.html#ViewBoxAttribute
				//.attr("viewBox", "0 -" + arrowWidth + " 10 " + (2 * arrowWidth))
				.attr("viewBox", "0 -5 10 10")
				// See also http://www.w3.org/TR/SVG/painting.html#MarkerElementRefXAttribute
				.attr("refX", 10)
				.attr("refY", 0)
				.attr("markerWidth", 10)
				.attr("markerHeight", arrowWidth)
				.append("path")
				.attr("d", "M0,-5L10,0L0,5");

			var simulation = d3.forceSimulation()
				.force("link", d3.forceLink())
				.force("charge", d3.forceManyBody().strength(-75))
				.force("center", d3.forceCenter(width / 2, height / 2));

			//set the distance between the nodes
			simulation.force("link")
				.distance(180)
				//nodes are slowing getting back on there place after they have been moved
				.strength(0.1);
			
			var drag = d3.drag()
				.on("start", dragstarted)
				.on("drag", dragged)
				.on("end", dragended);
      
			function dragstarted() {
				if (!d3.event.active) simulation.alphaTarget(0.3).restart();
				d3.event.subject.fx = d3.event.subject.x;
				d3.event.subject.fy = d3.event.subject.y;
			}

			function dragged() {
				d3.event.subject.fx = d3.event.x;
				d3.event.subject.fy = d3.event.y;
			}

			function dragended() {
				if (!d3.event.active) simulation.alphaTarget(0);
				d3.event.subject.fx = null;
				d3.event.subject.fy = null;
			}

			function flatten(graph) {
				// hierarchical data to flat data for force layout
				var nodes = [];
				var i = 0;
				function recurse(node) {
					if (node.children) node.children.forEach(recurse);
					if (!node.id) node.id = ++i;
					else ++i;
					nodes.push(node);
				}
				recurse(graph);
				return nodes;
			}
      
			function render(graph){

				var link = linkG.selectAll("line").data(graph.links);
				var linkEnter = link.enter().append("line")
					.attr("class", "link-line");
				link.exit().remove();
				link = link.merge(linkEnter);

				var arrow = arrowG.selectAll("line").data(graph.links);
				var arrowEnter = arrow.enter().append("line")
					.attr("class", "arrow")
					.attr("marker-end", "url(#arrow)" );
				arrow.exit().remove();
				arrow = arrow.merge(arrowEnter);

				var node = nodeG.selectAll("g").data(graph.nodes);
				var nodeEnter = node.enter().append("g").call(drag);
				node.exit().remove();

				nodeEnter.append("rect")
					.attr("class", "node-rect")
					.attr("y", -nodeSize)
					.attr("height", nodeSize * 2)
					.attr("rx", nodeSize)
					.attr("id", function(d) { return d.id; })
					.attr("ry", nodeSize)
					//single click function on node
					.on("click", function (d){
						//TODO: fix simulation.unfix - unknown function
						//simulation.unfix(d);
						var id = $(this).attr('id');

						$(".node-rect").attr("class", "node-rect");

						$(this).attr("class", "node-rect selected");

						$.getJSON('{{ URL::to('/') }}/api/terms/' + id, function(graph) {
							$('dd#term_name').text(graph.term.term_name);
							$('dd#term_glossary_name').text(graph.term.glossary_name);
							$('dd#term_description').text(graph.term.term_description);
							$('dd#term_owner').text(graph.term.owner_firstname + ' ' + graph.term.owner_lastname);
							$('dd#term_status').text(graph.term.status_name);
						});
					})
					//double click function on node
					.on("dblclick", function (d){
						var id = $(this).attr('id');
						console.log('double click: ' + id);
					});

				nodeEnter.append("text")
					.attr("class", "node-text")
					.attr("id", function(d) { return d.id; });

				node = node.merge(nodeEnter);

				node.select(".node-text")
					.text(function (d) { return d.term_name; })
					.each(function (d) {

						var circleWidth = nodeSize * 2,
							textLength = this.getComputedTextLength(),
							textWidth = textLength + nodeSize;

						if(circleWidth > textWidth) {
						d.isCircle = true;
						d.rectX = -nodeSize;
						d.rectWidth = circleWidth;
						} else {
						d.isCircle = false;
						d.rectX = -(textLength + nodeSize) / 2;
						d.rectWidth = textWidth;
						d.textLength = textLength;
						}
					});

				node.select(".node-rect")
					.attr("x", function(d) { return d.rectX; })
					.attr("width", function(d) { return d.rectWidth; });

				//TODO: make link text visible
				link.append("text")
					.attr("text-anchor", "middle")
					.text(function(d) {
						console.log(d.link_name);
						 return d.link_name;
					});

				simulation.force("link").links(graph.links);

				simulation.nodes(graph.nodes).on("tick", function (){

					graph.nodes.forEach(function (d) {
						if(d.isCircle){
							d.leftX = d.rightX = d.x;
						} else {
							d.leftX =  d.x - d.textLength / 2 + nodeSize / 2;
							d.rightX = d.x + d.textLength / 2 - nodeSize / 2;
						}
					});

					link.call(edge);
					arrow.call(edge);

					node.attr("transform", function(d) {      
						return "translate(" + d.x + "," + d.y + ")";
					});
				});

				//highlight the first element, this is the first and selected element from the array
				$("rect#1").css("fill","#E7FFE7");
				
			}

			// Sets the (x1, y1, x2, y2) line properties for graph edges.
			function edge(selection){
				selection
				.each(function (d) {
					var sourceX, targetX, midX, dy, dy, angle;

					// This mess makes the arrows exactly perfect.
					if( d.source.rightX < d.target.leftX ){
						sourceX = d.source.rightX;
						targetX = d.target.leftX;
					} else if( d.target.rightX < d.source.leftX ){
						targetX = d.target.rightX;
						sourceX = d.source.leftX;
					} else if (d.target.isCircle) {
						targetX = sourceX = d.target.x;
					} else if (d.source.isCircle) {
						targetX = sourceX = d.source.x;
					} else {
						midX = (d.source.x + d.target.x) / 2;
						if(midX > d.target.rightX){
							midX = d.target.rightX;
						} else if(midX > d.source.rightX){
							midX = d.source.rightX;
						} else if(midX < d.target.leftX){
							midX = d.target.leftX;
						} else if(midX < d.source.leftX){
							midX = d.source.leftX;
						}
						targetX = sourceX = midX;
					}

					dx = targetX - sourceX;
					dy = d.target.y - d.source.y;
					angle = Math.atan2(dx, dy);

					// Compute the line endpoint such that the arrow
					// is touching the edge of the node rectangle perfectly.
					d.sourceX = sourceX + Math.sin(angle) * nodeSize;
					d.targetX = targetX - Math.sin(angle) * nodeSize;
					d.sourceY = d.source.y + Math.cos(angle) * nodeSize;
					d.targetY = d.target.y - Math.cos(angle) * nodeSize;
				})
				.attr("x1", function(d) { return d.sourceX; })
				.attr("y1", function(d) { return d.sourceY; })
				.attr("x2", function(d) { return d.targetX; })
				.attr("y2", function(d) { return d.targetY; });
			}

			//use map function to add coordinates on nodes
			graph.nodes = graph.nodes.map(function (d){
				return d;
			});

			//use map function to add coordinates on links
			graph.links = graph.links.map(function (d){
				d.source = graph.nodes[d.source];
				d.target = graph.nodes[d.target];
				return d;
			});

			str = JSON.stringify(graph.nodes);
			str = JSON.stringify(graph.nodes, null, 4); // (Optional) beautiful indented output.
			//console.log(str); // Logs output to dev tools console.
			
			str2 = JSON.stringify(graph.links);
			str2 = JSON.stringify(graph.links, null, 4); // (Optional) beautiful indented output.
			//console.log(str2); // Logs output to dev tools console.

			render(graph);

		});
	});

	</script>

@endsection
