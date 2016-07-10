<!-- /resources/views/terms/show.blade.php -->
@extends('layouts.master')

@section('content')

	<head>
	<script src="{{ URL::asset('js/d3.v4.1.0.min.js') }}"></script>
	<link rel="stylesheet" href="{{ URL::asset('css/font-awesome.min.css') }}">
	</head>
	
	<style>
	line {
		stroke: #666;
		stroke-width: 2px;
		stroke-opacity: 0.6;
	}

	.node {
		pointer-events: all;
	}

	circle {
		stroke: none;
		stroke-width: 40px;
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

	<dl class="dl-horizontal">
	<dt>Term name:</dt>
	<dd>{{ $term->term_name }}</dd>
	</dl>
	
	<dl class="dl-horizontal">
	<dt>Term definition:</dt>
	<dd>{!! App\Helper::contentAdjust($term->term_description) !!}</dd>
	</dl>
	
	<div id="spinner" style="text-align: center; margin-top:100px;">
	<p>Loading...</p>
	<i class="fa fa-spinner fa-spin" style="font-size:24px"></i>
	<div>
	<svg class="main"></svg>

	<script>
	
	$( document ).ready(function() {
		
		// Define the dimensions of the visualization. We're using
		// a size that's convenient for displaying the graphic on
		// http://jsDataV.is

		var width = 960,
			height = 450;

		$.getJSON('{{ URL::to('/') }}/api/terms/{{ $term->id }}', function(root) {

			//initialising hierarchical data
			root = d3.hierarchy(root);
			
			//remove spinner when the load is completed
			$("#spinner").hide();

			var i = 0;

			var transform = d3.zoomIdentity;;

			// Next define the main object for the layout. We'll also
			// define a couple of objects to keep track of the D3 selections
			// for the nodes and the links. All of these objects are
			// initialized later on.

			var nodeSvg, linkSvg, simulation, nodeEnter, linkEnter;

			// We can also create the SVG container that will hold the
			// visualization. D3 makes it easy to set this container's
			// dimensions and add it to the DOM.

			var svg = d3.select("div.container").append("svg")
				.attr("width", "100%")
				.attr("height", 450)
				.attr("id", 'd3js')
				.call(d3.zoom().scaleExtent([1 / 2, 8]).on("zoom", zoomed))
				.append("g")
				.attr("transform", "translate(40,0)");
				
			// Per-type arrow markers, as they don't inherit styles.
			svg.append("svg:defs").append("svg:marker")
				.attr("id", "triangle")
				.attr("refX", 13)
				.attr("refY", 6)
				.attr("markerWidth", 30)
				.attr("markerHeight", 30)
				.attr("orient", "auto")
				.append("path")
				.style("fill", "#666")
				.style("stroke-opacity", "0.6")
				.attr("d", "M 2 3 8 6 2 8 2 0");

			//Append a defs (for definition) element to your SVG
			var defs = svg.append("defs");

			//Append a linearGradient element to the defs and give it a unique id
			var linearGradient = defs.append("linearGradient")
				.attr("id", "linear-gradient");

			function zoomed() {
				svg.attr("transform", d3.event.transform);
			}

			simulation = d3.forceSimulation()
				.force("link", d3.forceLink().distance(100).strength(2).id(function(d) {
					return d.id;
				}))
				.force("charge", d3.forceManyBody())
				//centre the object a bit from the middle of the svg (2=middle)
				.force("center", d3.forceCenter(width / 3, height / 3))
				.on("tick", ticked);

			update();

			function update() {
				var nodes = flatten(root);
				var links = root.links();

				linkSvg = svg.selectAll(".link")
					.data(links, function(d) {
						return d.target.id;
					})

				linkSvg.exit().remove();

				var linkEnter = linkSvg.enter()
					.append("line")
					.attr("class", "link")
					//added an arrow, see per-type arrow markers added
					.attr("marker-end", "url(#triangle)");

				linkSvg = linkEnter.merge(linkSvg)

				nodeSvg = svg.selectAll(".node")
					.data(nodes, function(d) {
						return d.id;
					})

				nodeSvg.exit().remove();

				var nodeEnter = nodeSvg.enter()
					.append("g")
					.attr("class", "node")
					.on("click", click)
					.call(d3.drag()
						.on("start", dragstarted)
						.on("drag", dragged)
						.on("end", dragended))

				//Diagonal gradient where the start and end point 
				//have been pulled in
				linearGradient
					.attr("x1", "30%")
					.attr("y1", "30%")
					.attr("x2", "70%")
					.attr("y2", "70%");

				//Set the color for the start (0%)
				linearGradient.append("stop") 
					.attr("offset", "0%")   
					.attr("stop-color", "#808080"); //light dark grey

				//Set the color for the end (100%)
				linearGradient.append("stop") 
					.attr("offset", "100%")   
					.attr("stop-color", "#333333"); //dark grey

				nodeEnter.append("circle")
					//size of circle
					.attr("r", 12)
					.style("fill", "url(#linear-gradient)")
					.append("title")
					.text(function(d) {
						return d.data.term_name;
					})

				nodeEnter.append("text")
					.attr("dy", 3)
					.attr("x", function(d) {
						//position of the text next to circle
						return d.children ? -16 : 16;
					})
					.style("text-anchor", function(d) {
						return d.children ? "end" : "start";
					})
					.text(function(d) {
						return d.data.term_name;
					});

				nodeSvg = nodeEnter.merge(nodeSvg);

				simulation
					.nodes(nodes)

				simulation.force("link")
					.links(links);

			}

			function ticked() {
				linkSvg
					.attr("x1", function(d) {
						return d.source.x;
					})
					.attr("y1", function(d) {
						return d.source.y;
					})
					.attr("x2", function(d) {
						return d.target.x;
					})
					.attr("y2", function(d) {
						return d.target.y;
					});

				nodeSvg
					.attr("transform", function(d) {
						return "translate(" + d.x + ", " + d.y + ")";
					});
			}

			function click(d) {
				if (d.children) {
					d._children = d.children;
					d.children = null;
					update();
					simulation.restart();
				} else {
					d.children = d._children;
					d._children = null;
					update();
					simulation.restart();
				}
			}

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

			function flatten(root) {
				// hierarchical data to flat data for force layout
				var nodes = [];

				function recurse(node) {
					if (node.children) node.children.forEach(recurse);
					if (!node.id) node.id = ++i;
					else ++i;
					nodes.push(node);
				}
				recurse(root);
				return nodes;
			}
		});
	});

	</script>

@endsection
