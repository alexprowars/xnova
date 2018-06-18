
function ECONode (id, pid, dsc, w, h, c, linkc, bc, target, meta) {
	this.id = id;
	this.pid = pid;
	this.dsc = dsc;
	this.w = w;
	this.h = h;
	this.c = c;
	this.linkc = linkc;
	this.bc = bc;
	this.target = target;
	this.meta = meta;
	
	this.siblingIndex = 0;
	this.dbIndex = 0;
	
	this.XPosition = 0;
	this.YPosition = 0;
	this.prelim = 0;
	this.modifier = 0;
	this.leftNeighbor = null;
	this.rightNeighbor = null;
	this.nodeParent = null;	
	this.nodeChildren = [];
	
	this.isCollapsed = false;
	this.canCollapse = false;
	
	this.isSelected = false;
}

ECONode.prototype._getLevel = function () {
	if (this.nodeParent.id === -1) {return 0;}
	else return this.nodeParent._getLevel() + 1;
}

ECONode.prototype._isAncestorCollapsed = function () {
	if (this.nodeParent.isCollapsed) { return true; }
	else 
	{
		if (this.nodeParent.id === -1) { return false; }
		else	{ return this.nodeParent._isAncestorCollapsed(); }
	}
}

ECONode.prototype._setAncestorsExpanded = function () {
	if (this.nodeParent.id === -1) { return; }
	else 
	{
		this.nodeParent.isCollapsed = false;
		return this.nodeParent._setAncestorsExpanded(); 
	}	
}

ECONode.prototype._getChildrenCount = function () {
	if (this.isCollapsed) return 0;
    if(this.nodeChildren == null)
        return 0;
    else
        return this.nodeChildren.length;
}

ECONode.prototype._getLeftSibling = function () {
    if(this.leftNeighbor != null && this.leftNeighbor.nodeParent === this.nodeParent)
        return this.leftNeighbor;
    else
        return null;	
}

ECONode.prototype._getRightSibling = function () {
    if(this.rightNeighbor != null && this.rightNeighbor.nodeParent === this.nodeParent)
        return this.rightNeighbor;
    else
        return null;	
}

ECONode.prototype._getChildAt = function (i) {
	return this.nodeChildren[i];
}

ECONode.prototype._getChildrenCenter = function (tree) {
    let node = this._getFirstChild();
	let node1 = this._getLastChild();
    return node.prelim + ((node1.prelim - node.prelim) + tree._getNodeSize(node1)) / 2;	
}

ECONode.prototype._getFirstChild = function () {
	return this._getChildAt(0);
}

ECONode.prototype._getLastChild = function () {
	return this._getChildAt(this._getChildrenCount() - 1);
}

ECONode.prototype._drawChildrenLinks = function (tree) {
	let s = [];
	let xa = 0, ya = 0, xb = 0, yb = 0, xc = 0, yc = 0, xd = 0, yd = 0;
	let node1 = null;
	
	switch(tree.config.iRootOrientation)
	{
		case ECOTree.RO_TOP:
			xa = this.XPosition + (this.w / 2);
			ya = this.YPosition + this.h;
			break;
			
		case ECOTree.RO_BOTTOM:
			xa = this.XPosition + (this.w / 2);
			ya = this.YPosition;
			break;
			
		case ECOTree.RO_RIGHT:
			xa = this.XPosition;
			ya = this.YPosition + (this.h / 2);		
			break;
			
		case ECOTree.RO_LEFT:
			xa = this.XPosition + this.w;
			ya = this.YPosition + (this.h / 2);		
			break;		
	}
	
	for (let k = 0; k < this.nodeChildren.length; k++)
	{
		node1 = this.nodeChildren[k];
				
		switch(tree.config.iRootOrientation)
		{
			case ECOTree.RO_TOP:
				xd = xc = node1.XPosition + (node1.w / 2);
				yd = node1.YPosition;
				xb = xa;
				switch (tree.config.iNodeJustification)
				{
					case ECOTree.NJ_TOP:
						yb = yc = yd - tree.config.iLevelSeparation / 2;
						break;
					case ECOTree.NJ_BOTTOM:
						yb = yc = ya + tree.config.iLevelSeparation / 2;
						break;
					case ECOTree.NJ_CENTER:
						yb = yc = ya + (yd - ya) / 2;
						break;
				}
				break;
				
			case ECOTree.RO_BOTTOM:
				xd = xc = node1.XPosition + (node1.w / 2);
				yd = node1.YPosition + node1.h;
				xb = xa;
				switch (tree.config.iNodeJustification)
				{
					case ECOTree.NJ_TOP:
						yb = yc = yd + tree.config.iLevelSeparation / 2;
						break;
					case ECOTree.NJ_BOTTOM:
						yb = yc = ya - tree.config.iLevelSeparation / 2;
						break;
					case ECOTree.NJ_CENTER:
						yb = yc = yd + (ya - yd) / 2;
						break;
				}				
				break;

			case ECOTree.RO_RIGHT:
				xd = node1.XPosition + node1.w;
				yd = yc = node1.YPosition + (node1.h / 2);	
				yb = ya;
				switch (tree.config.iNodeJustification)
				{
					case ECOTree.NJ_TOP:
						xb = xc = xd + tree.config.iLevelSeparation / 2;
						break;
					case ECOTree.NJ_BOTTOM:
						xb = xc = xa - tree.config.iLevelSeparation / 2;
						break;
					case ECOTree.NJ_CENTER:
						xb = xc = xd + (xa - xd) / 2;
						break;
				}								
				break;		
				
			case ECOTree.RO_LEFT:
				xd = node1.XPosition;
				yd = yc = node1.YPosition + (node1.h / 2);		
				yb = ya;
				switch (tree.config.iNodeJustification)
				{
					case ECOTree.NJ_TOP:
						xb = xc = xd - tree.config.iLevelSeparation / 2;
						break;
					case ECOTree.NJ_BOTTOM:
						xb = xc = xa + tree.config.iLevelSeparation / 2;
						break;
					case ECOTree.NJ_CENTER:
						xb = xc = xa + (xd - xa) / 2;
						break;
				}								
				break;				
		}		
		
		
		tree.ctx.save();
		//tree.ctx.strokeStyle = tree.config.linkColor;
		tree.ctx.lineWidth = 3;
		//tree.ctx.shadowOffsetX = 2;
		//tree.ctx.shadowOffsetY = 2;
		//tree.ctx.shadowBlur = 20;
		//tree.ctx.shadowColor = node1.linkc;
		tree.ctx.strokeStyle = node1.linkc;
		tree.ctx.beginPath();			
		switch (tree.config.linkType)
			{
			case "M":						
				tree.ctx.moveTo(xa,ya);
				tree.ctx.lineTo(xb,yb);
				tree.ctx.lineTo(xc,yc);
				tree.ctx.lineTo(xd,yd);						
				break;
				
			case "B":
				tree.ctx.moveTo(xa,ya);
				tree.ctx.bezierCurveTo(xb,yb,xc,yc,xd,yd);	
				break;					
			}
		tree.ctx.stroke();
		tree.ctx.restore();
		//break;

	}	
	
	return s.join('');
}
function ECOTree (obj, elm) {
	this.config = {
		iMaxDepth : 100,
		iLevelSeparation : 60,
		iSiblingSeparation : 50,
		iSubtreeSeparation : 45,
		iRootOrientation : ECOTree.RO_LEFT,
		iNodeJustification : ECOTree.NJ_TOP,
		topXAdjustment : 30,
		topYAdjustment : 0,		
		render : "CANVAS",
		linkType : "B",
		linkColor : "red",
		nodeColor : "#FFD700",
		nodeFill : ECOTree.NF_FLAT,
		nodeBorderColor : "red",
		nodeSelColor : "#FFFFCC",
		levelColors : ["#5555FF","#8888FF","#AAAAFF","#CCCCFF"],
		levelBorderColors : ["#5555FF","#8888FF","#AAAAFF","#CCCCFF"],
		colorStyle : ECOTree.CS_NODE,
		useTarget : false,
		searchMode : ECOTree.SM_DSC,
		selectMode : ECOTree.SL_NONE,
		defaultNodeWidth : 55,
		defaultNodeHeight : 55,
		defaultTarget : 'javascript:void(0);',
		expandedImage : './design/images/pixel.png',
		collapsedImage : './design/images/pixel.png',
		transImage : './design/images/pixel.png'
	}
	
	this.version = "1.1";
	this.obj = obj;
	this.elm = document.getElementById(elm);
	this.self = this;
	this.render = "CANVAS";
	this.ctx = null;
	this.canvasoffsetTop = 0;
	this.canvasoffsetLeft = 0;
	
	this.maxLevelHeight = [];
	this.maxLevelWidth = [];
	this.previousLevelNode = [];
	
	this.rootYOffset = 0;
	this.rootXOffset = 0;
	
	this.nDatabaseNodes = [];
	this.mapIDs = {};
	
	this.root = new ECONode(-1, null, null, 2, 2);
	this.iSelectedNode = -1;
	this.iLastSearch = 0;
	
}

//Constant values

//Tree orientation
ECOTree.RO_TOP = 0;
ECOTree.RO_BOTTOM = 1;
ECOTree.RO_RIGHT = 2;
ECOTree.RO_LEFT = 3;

//Level node alignment
ECOTree.NJ_TOP = 0;
ECOTree.NJ_CENTER = 1;
ECOTree.NJ_BOTTOM = 2;

//Node fill type
ECOTree.NF_GRADIENT = 0;
ECOTree.NF_FLAT = 1;

//Colorizing style
ECOTree.CS_NODE = 0;
ECOTree.CS_LEVEL = 1;

//Search method: Title, metadata or both
ECOTree.SM_DSC = 0;
ECOTree.SM_META = 1;
ECOTree.SM_BOTH = 2;

//Selection mode: single, multiple, no selection
ECOTree.SL_MULTIPLE = 0;
ECOTree.SL_SINGLE = 1;
ECOTree.SL_NONE = 2;

//CANVAS functions...
ECOTree._roundedRect = function (ctx,x,y,width,height,radius) {
  ctx.beginPath();
  ctx.moveTo(x,y+radius);
  ctx.lineTo(x,y+height-radius);
  ctx.quadraticCurveTo(x,y+height,x+radius,y+height);
  ctx.lineTo(x+width-radius,y+height);
  ctx.quadraticCurveTo(x+width,y+height,x+width,y+height-radius);
  ctx.lineTo(x+width,y+radius);
  ctx.quadraticCurveTo(x+width,y,x+width-radius,y);
  ctx.lineTo(x+radius,y);
  ctx.quadraticCurveTo(x,y,x,y+radius);
  ctx.fill();
  ctx.stroke();
}

ECOTree._canvasNodeClickHandler = function (tree,target,nodeid) {
	if (target !== nodeid) return;
	tree.selectNode(nodeid,true);
}

//Layout algorithm
ECOTree._firstWalk = function (tree, node, level) {
		let leftSibling = null;
		
        node.XPosition = 0;
        node.YPosition = 0;
        node.prelim = 0;
        node.modifier = 0;
        node.leftNeighbor = null;
        node.rightNeighbor = null;
        tree._setLevelHeight(node, level);
        tree._setLevelWidth(node, level);
        tree._setNeighbors(node, level);
        if(node._getChildrenCount() === 0 || level === tree.config.iMaxDepth)
        {
            leftSibling = node._getLeftSibling();
            if(leftSibling != null)
                node.prelim = leftSibling.prelim + tree._getNodeSize(leftSibling) + tree.config.iSiblingSeparation;
            else
                node.prelim = 0;
        } 
        else
        {
            let n = node._getChildrenCount();
            for(let i = 0; i < n; i++)
            {
                let iChild = node._getChildAt(i);
                ECOTree._firstWalk(tree, iChild, level + 1);
            }

            let midPoint = node._getChildrenCenter(tree);
            midPoint -= tree._getNodeSize(node) / 2;
            leftSibling = node._getLeftSibling();
            if(leftSibling != null)
            {
                node.prelim = leftSibling.prelim + tree._getNodeSize(leftSibling) + tree.config.iSiblingSeparation;
                node.modifier = node.prelim - midPoint;
                ECOTree._apportion(tree, node, level);
            } 
            else
            {            	
                node.prelim = midPoint;
            }
        }	
}

ECOTree._apportion = function (tree, node, level) {
        let firstChild = node._getFirstChild();
        let firstChildLeftNeighbor = firstChild.leftNeighbor;
        let j = 1;
        for(let k = tree.config.iMaxDepth - level; firstChild != null && firstChildLeftNeighbor != null && j <= k;)
        {
            let modifierSumRight = 0;
            let modifierSumLeft = 0;
            let rightAncestor = firstChild;
            let leftAncestor = firstChildLeftNeighbor;
            for(let l = 0; l < j; l++)
            {
                rightAncestor = rightAncestor.nodeParent;
                leftAncestor = leftAncestor.nodeParent;
                modifierSumRight += rightAncestor.modifier;
                modifierSumLeft += leftAncestor.modifier;
            }

            let totalGap = (firstChildLeftNeighbor.prelim + modifierSumLeft + tree._getNodeSize(firstChildLeftNeighbor) + tree.config.iSubtreeSeparation) - (firstChild.prelim + modifierSumRight);
            if(totalGap > 0)
            {
                let subtreeAux = node;
                let numSubtrees = 0;
                for(; subtreeAux != null && subtreeAux !== leftAncestor; subtreeAux = subtreeAux._getLeftSibling())
                    numSubtrees++;

                if(subtreeAux != null)
                {
                    let subtreeMoveAux = node;
                    let singleGap = totalGap / numSubtrees;
                    for(; subtreeMoveAux !== leftAncestor; subtreeMoveAux = subtreeMoveAux._getLeftSibling())
                    {
                        subtreeMoveAux.prelim += totalGap;
                        subtreeMoveAux.modifier += totalGap;
                        totalGap -= singleGap;
                    }

                }
            }
            j++;
            if(firstChild._getChildrenCount() === 0)
                firstChild = tree._getLeftmost(node, 0, j);
            else
                firstChild = firstChild._getFirstChild();
            if(firstChild != null)
                firstChildLeftNeighbor = firstChild.leftNeighbor;
        }
}

ECOTree._secondWalk = function (tree, node, level, X, Y) {
        if(level <= tree.config.iMaxDepth)
        {
            let xTmp = tree.rootXOffset + node.prelim + X;
            let yTmp = tree.rootYOffset + Y;
            let maxsizeTmp = 0;
            let nodesizeTmp = 0;
            let flag = false;
            
            switch(tree.config.iRootOrientation)
            {            
	            case ECOTree.RO_TOP:
	            case ECOTree.RO_BOTTOM:	        	            	    	
	                maxsizeTmp = tree.maxLevelHeight[level];
	                nodesizeTmp = node.h;	                
	                break;

	            case ECOTree.RO_RIGHT:
	            case ECOTree.RO_LEFT:            
	                maxsizeTmp = tree.maxLevelWidth[level];
	                flag = true;
	                nodesizeTmp = node.w;
	                break;
            }
            switch(tree.config.iNodeJustification)
            {
	            case ECOTree.NJ_TOP:
	                node.XPosition = xTmp;
	                node.YPosition = yTmp;
	                break;
	
	            case ECOTree.NJ_CENTER:
	                node.XPosition = xTmp;
	                node.YPosition = yTmp + (maxsizeTmp - nodesizeTmp) / 2;
	                break;
	
	            case ECOTree.NJ_BOTTOM:
	                node.XPosition = xTmp;
	                node.YPosition = (yTmp + maxsizeTmp) - nodesizeTmp;
	                break;
            }
            if(flag)
            {
                let swapTmp = node.XPosition;
                node.XPosition = node.YPosition;
                node.YPosition = swapTmp;
            }
            switch(tree.config.iRootOrientation)
            {
	            case ECOTree.RO_BOTTOM:
	                node.YPosition = -node.YPosition - nodesizeTmp;
	                break;
	
	            case ECOTree.RO_RIGHT:
	                node.XPosition = -node.XPosition - nodesizeTmp;
	                break;
            }
            if(node._getChildrenCount() !== 0)
                ECOTree._secondWalk(tree, node._getFirstChild(), level + 1, X + node.modifier, Y + maxsizeTmp + tree.config.iLevelSeparation);
            let rightSibling = node._getRightSibling();
            if(rightSibling != null)
                ECOTree._secondWalk(tree, rightSibling, level, X, Y);
        }	
}

ECOTree.prototype._positionTree = function () {	
	this.maxLevelHeight = [];
	this.maxLevelWidth = [];			
	this.previousLevelNode = [];		
	ECOTree._firstWalk(this.self, this.root, 0);
	
	switch(this.config.iRootOrientation)
	{            
	    case ECOTree.RO_TOP:
	    case ECOTree.RO_LEFT: 
	    		this.rootXOffset = this.config.topXAdjustment + this.root.XPosition;
	    		this.rootYOffset = this.config.topYAdjustment + this.root.YPosition;
	        break;    
	        
	    case ECOTree.RO_BOTTOM:	
	    case ECOTree.RO_RIGHT:             
	    		this.rootXOffset = this.config.topXAdjustment + this.root.XPosition;
	    		this.rootYOffset = this.config.topYAdjustment + this.root.YPosition;
	}	
	
	ECOTree._secondWalk(this.self, this.root, 0, 0, 0);	
}

ECOTree.prototype._setLevelHeight = function (node, level) {	
	if (this.maxLevelHeight[level] == null) 
		this.maxLevelHeight[level] = 0;
    if(this.maxLevelHeight[level] < node.h)
        this.maxLevelHeight[level] = node.h;	
}

ECOTree.prototype._setLevelWidth = function (node, level) {
	if (this.maxLevelWidth[level] == null) 
		this.maxLevelWidth[level] = 0;
    if(this.maxLevelWidth[level] < node.w)
        this.maxLevelWidth[level] = node.w;		
}

ECOTree.prototype._setNeighbors = function(node, level) {
    node.leftNeighbor = this.previousLevelNode[level];
    if(node.leftNeighbor != null)
        node.leftNeighbor.rightNeighbor = node;
    this.previousLevelNode[level] = node;	
}

ECOTree.prototype._getNodeSize = function (node) {
    switch(this.config.iRootOrientation)
    {
    case ECOTree.RO_TOP: 
    case ECOTree.RO_BOTTOM: 
        return node.w;

    case ECOTree.RO_RIGHT: 
    case ECOTree.RO_LEFT: 
        return node.h;
    }
    return 0;
}

ECOTree.prototype._getLeftmost = function (node, level, maxlevel) {
    if(level >= maxlevel) return node;
    if(node._getChildrenCount() === 0) return null;
    
    let n = node._getChildrenCount();
    for(let i = 0; i < n; i++)
    {
        let iChild = node._getChildAt(i);
        let leftmostDescendant = this._getLeftmost(iChild, level + 1, maxlevel);
        if(leftmostDescendant != null)
            return leftmostDescendant;
    }

    return null;	
}

ECOTree.prototype._selectNodeInt = function (dbindex, flagToggle) {
	if (this.config.selectMode === ECOTree.SL_SINGLE)
	{
		if ((this.iSelectedNode !== dbindex) && (this.iSelectedNode != -1))
		{
			this.nDatabaseNodes[this.iSelectedNode].isSelected = false;
		}		
		this.iSelectedNode = (this.nDatabaseNodes[dbindex].isSelected && flagToggle) ? -1 : dbindex;
	}	
	this.nDatabaseNodes[dbindex].isSelected = (flagToggle) ? !this.nDatabaseNodes[dbindex].isSelected : true;	
}

ECOTree.prototype._collapseAllInt = function (flag) {
	let node = null;
	for (let n = 0; n < this.nDatabaseNodes.length; n++)
	{ 
		node = this.nDatabaseNodes[n];
		if (node.canCollapse) node.isCollapsed = flag;
	}	
	this.UpdateTree();
}

ECOTree.prototype._selectAllInt = function (flag) {
	let node = null;
	for (let k = 0; k < this.nDatabaseNodes.length; k++)
	{ 
		node = this.nDatabaseNodes[k];
		node.isSelected = flag;
	}	
	this.iSelectedNode = -1;
	this.UpdateTree();
}

ECOTree.prototype._drawTree = function () {
	let s = [];
	let node = null;
	let color = "";
	let border = "";
			
	for (let n = 0; n < this.nDatabaseNodes.length; n++)
	{ 
		node = this.nDatabaseNodes[n];
		
		switch (this.config.colorStyle) {
			case ECOTree.CS_NODE:
				color = node.c;
				border = node.bc;
				break;
			case ECOTree.CS_LEVEL:
				let iColor = node._getLevel() % this.config.levelColors.length;
				color = this.config.levelColors[iColor];
				iColor = node._getLevel() % this.config.levelBorderColors.length;
				border = this.config.levelBorderColors[iColor];
				break;
		}
		
		if (!node._isAncestorCollapsed())
		{
			//Canvas part...
			this.ctx.save();
			this.ctx.strokeStyle = border;
			switch (this.config.nodeFill) {
				case ECOTree.NF_GRADIENT:							
					let lgradient = this.ctx.createLinearGradient(node.XPosition,0,node.XPosition+node.w,0);
					lgradient.addColorStop(0.0,((node.isSelected)?this.config.nodeSelColor:color));
					lgradient.addColorStop(1.0,"#F5FFF5");
					this.ctx.fillStyle = lgradient;
					break;
					
				case ECOTree.NF_FLAT:
					this.ctx.fillStyle = ((node.isSelected)?this.config.nodeSelColor:color);
					break;
				}					
					
			ECOTree._roundedRect(this.ctx,node.XPosition,node.YPosition,node.w,node.h,5);
			this.ctx.restore();
			
			//HTML part...
			s.push('<div id="' + node.id + '" class="tch_node" style="z-index:5;cursor:pointer;top:'+(node.YPosition+this.canvasoffsetTop)+'px; left:'+(node.XPosition+this.canvasoffsetLeft)+'px; width:'+node.w+'px; height:'+node.h+'px;" ');
			s.push('onclick="javascript:'+this.obj+'.collapseNode(\''+node.id+'\', true);" >');
			s.push(node.dsc);										
			s.push('</div>');		
			//break;
			
			if (!node.isCollapsed)	s.push(node._drawChildrenLinks(this.self));
		}
	}	
	return s.join('');	
}

ECOTree.prototype.toString = function () {	
	let s = [];
	
	this._positionTree();
	
	s.push('<canvas id="ECOTreecanvas" width=785 height=1000 ></canvas>');

	return s.join('');
}

// ECOTree API begins here...

ECOTree.prototype.UpdateTree = function () {	
	this.elm.innerHTML = this;
	if (this.render === "CANVAS") {
		let canvas = document.getElementById("ECOTreecanvas");
		if (canvas && canvas.getContext)  {
			this.canvasoffsetLeft = canvas.offsetLeft;
			this.canvasoffsetTop = canvas.offsetTop;
			this.ctx = canvas.getContext('2d');
			let h = this._drawTree();
			let r = this.elm.ownerDocument.createRange();
			r.setStartBefore(this.elm);
			let parsedHTML = r.createContextualFragment(h);
			//this.elm.parentNode.insertBefore(parsedHTML,this.elm)
			//this.elm.parentNode.appendChild(parsedHTML);
			this.elm.appendChild(parsedHTML);
			//this.elm.insertBefore(parsedHTML,this.elm.firstChild);
		}
	}
}

ECOTree.prototype.add = function (id, pid, dsc, w, h, c, linkc, bc, target, meta) {	
	let nw = w || this.config.defaultNodeWidth; //Width, height, colors, target and metadata defaults...
	let nh = h || this.config.defaultNodeHeight;
	let color = c || this.config.nodeColor;
	let linkclr = linkc || this.config.linkColor;
	let border = bc || this.config.nodeBorderColor;
	let tg = (this.config.useTarget) ? ((typeof target === "undefined") ? (this.config.defaultTarget) : target) : null;
	let metadata = (typeof meta !== "undefined")	? meta : "";
	
	let pnode = null; //Search for parent node in database
	if (pid === -1)
		{
			pnode = this.root;
		}
	else
		{
			for (let k = 0; k < this.nDatabaseNodes.length; k++)
			{
				if (this.nDatabaseNodes[k].id === pid)
				{
					pnode = this.nDatabaseNodes[k];
					break;
				}
			}	
		}
	
	let node = new ECONode(id, pid, dsc, nw, nh, color, linkclr, border, tg, metadata);	//New node creation...
	node.nodeParent = pnode;  //Set it's parent
	pnode.canCollapse = true; //It's obvious that now the parent can collapse	
	let i = this.nDatabaseNodes.length;	//Save it in database
	node.dbIndex = this.mapIDs[id] = i;	 
	this.nDatabaseNodes[i] = node;	
	let hs = pnode.nodeChildren.length; //Add it as child of it's parent
	node.siblingIndex = hs;
	pnode.nodeChildren[hs] = node;
}

ECOTree.prototype.searchNodes = function (str) {
	let node = null;
	let m = this.config.searchMode;
	let sm = (this.config.selectMode === ECOTree.SL_SINGLE);
	
	if (typeof str === "undefined") return;
	if (str === "") return;
	
	let found = false;
	let n = (sm) ? this.iLastSearch : 0;
	if (n === this.nDatabaseNodes.length) n = this.iLastSeach = 0;
	
	str = str.toLocaleUpperCase();
	
	for (; n < this.nDatabaseNodes.length; n++)
	{ 		
		node = this.nDatabaseNodes[n];				
		if (node.dsc.toLocaleUpperCase().indexOf(str) !== -1 && ((m === ECOTree.SM_DSC) || (m === ECOTree.SM_BOTH))) { node._setAncestorsExpanded(); this._selectNodeInt(node.dbIndex, false); found = true; }
		if (node.meta.toLocaleUpperCase().indexOf(str) !== -1 && ((m === ECOTree.SM_META) || (m === ECOTree.SM_BOTH))) { node._setAncestorsExpanded(); this._selectNodeInt(node.dbIndex, false); found = true; }
		if (sm && found) {this.iLastSearch = n + 1; break;}
	}	
	this.UpdateTree();	
}

ECOTree.prototype.selectAll = function () {
	if (this.config.selectMode !== ECOTree.SL_MULTIPLE) return;
	this._selectAllInt(true);
}

ECOTree.prototype.unselectAll = function () {
	this._selectAllInt(false);
}

ECOTree.prototype.collapseAll = function () {
	this._collapseAllInt(true);
}

ECOTree.prototype.expandAll = function () {
	this._collapseAllInt(false);
}

ECOTree.prototype.collapseNode = function (nodeid, upd) {
	let dbindex = this.mapIDs[nodeid];
	if (this.nDatabaseNodes[dbindex].canCollapse === true)
	{
		this.nDatabaseNodes[dbindex].isCollapsed = !this.nDatabaseNodes[dbindex].isCollapsed;
		if (upd) this.UpdateTree();
	}
}

ECOTree.prototype.selectNode = function (nodeid, upd) {		
	this._selectNodeInt(this.mapIDs[nodeid], true);
	if (upd) this.UpdateTree();
}

ECOTree.prototype.setNodeTitle = function (nodeid, title, upd) {
	let dbindex = this.mapIDs[nodeid];
	this.nDatabaseNodes[dbindex].dsc = title;
	if (upd) this.UpdateTree();
}

ECOTree.prototype.setNodeMetadata = function (nodeid, meta, upd) {
	let dbindex = this.mapIDs[nodeid];
	this.nDatabaseNodes[dbindex].meta = meta;
	if (upd) this.UpdateTree();
}

ECOTree.prototype.setNodeTarget = function (nodeid, target, upd) {
	let dbindex = this.mapIDs[nodeid];
	this.nDatabaseNodes[dbindex].target = target;
	if (upd) this.UpdateTree();	
}

ECOTree.prototype.setNodeColors = function (nodeid, color, border, upd) {
	let dbindex = this.mapIDs[nodeid];
	if (color) this.nDatabaseNodes[dbindex].c = color;
	if (border) this.nDatabaseNodes[dbindex].bc = border;
	if (upd) this.UpdateTree();	
}

ECOTree.prototype.getSelectedNodes = function () {
	let node = null;
	let selection = [];
	let selnode = null;
	
	for (let n=0; n<this.nDatabaseNodes.length; n++) {
		node = this.nDatabaseNodes[n];
		if (node.isSelected)
		{			
			selnode = {
				"id" : node.id,
				"dsc" : node.dsc,
				"meta" : node.meta
			}
			selection[selection.length] = selnode;
		}
	}
	return selection;
}

export {
	ECOTree,
	ECONode
};