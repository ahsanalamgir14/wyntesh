<template>
    <li>
        <a href="javascript:void(0);">
            <div class="member-view-box">
                <div class="member-image">
                    <img src="@/assets/images/tree-user.png" alt="Member">
                    <div class="member-details">
                        <h3>{{node.user?node.user.name:''}}</h3>
                        <h3>{{node.user?node.user.username:''}}</h3>
                    </div>
                </div>
            </div>
        </a>
        <ul class="active">
            <node v-for="child in node.children" :key="node.id" :node="child"></node>
            <li v-for="index in totalLegs">
                <a href="javascript:void(0);" >
                    <div class="member-view-box">
                        <div class="member-image">
                            <img src="@/assets/images/tree-user.png" alt="Member">
                            <div class="member-details">
                                <h3>Place here</h3>
                            </div>
                        </div>
                    </div>
                </a>
            </li>
        </ul>
    </li>
      <!-- <li>
        <a href="#"   >
          <img src="@/assets/images/tree-user.png" class="rounded-circle avatarImg" :alt="node.user?node.user.username:''">
          <div class="business-tree-user-info">
            <p class="mb-0 font-weight-bold">{{node.user?node.user.name:''}}</p>
          </div>
        </a>
        <div>
          <el-tag
            type="primary"
            effect="dark">
            {{node.user?node.user.username:''}}
          </el-tag>
        </div>

        <ul v-if="node.children && node.children.length">
          <node v-for="child in node.children" :node="child"></node>
        </ul>
      </li> -->
    </ul>
  </div>
</template>

<script>
export default {
  name: "node",
  props: {
    node: Object
  },
  created(){
    if(this.node.id){
      this.children=this.node.children.length;  
      this.totalLegs=2;
      this.totalLegs=this.totalLegs-this.children;
    }
    
  },
  data() {
    return {   
      placehere:2,
      totalLegs:0,
      children:0
    };
  },
};
</script>

<style scoped>

/*----------------genealogy-scroll----------*/

.genealogy-scroll::-webkit-scrollbar {
    width: 5px;
    height: 8px;
}
.genealogy-scroll::-webkit-scrollbar-track {
    border-radius: 10px;
    background-color: #e4e4e4;
}
.genealogy-scroll::-webkit-scrollbar-thumb {
    background: #212121;
    border-radius: 10px;
    transition: 0.5s;
}
.genealogy-scroll::-webkit-scrollbar-thumb:hover {
    background: #d5b14c;
    transition: 0.5s;
}


/*----------------genealogy-tree----------*/
.genealogy-body{
    white-space: nowrap;
    overflow-y: hidden;
    padding: 50px;
    min-height: 500px;
    padding-top: 10px;
}
.genealogy-tree ul {
    padding-top: 20px; 
    position: relative;
    padding-left: 0px;
    display: flex;
}
.genealogy-tree li {
    float: left; text-align: center;
    list-style-type: none;
    position: relative;
    padding: 20px 5px 0 5px;
}
.genealogy-tree li::before, .genealogy-tree li::after{
    content: '';
    position: absolute; 
  top: 0; 
  right: 50%;
    border-top: 2px solid #ccc;
    width: 50%; 
  height: 18px;
}
.genealogy-tree li::after{
    right: auto; left: 50%;
    border-left: 2px solid #ccc;
}
.genealogy-tree li:only-child::after, .genealogy-tree li:only-child::before {
    display: none;
}
.genealogy-tree li:only-child{ 
    padding-top: 0;
}
.genealogy-tree li:first-child::before, .genealogy-tree li:last-child::after{
    border: 0 none;
}
.genealogy-tree li:last-child::before{
    border-right: 2px solid #ccc;
    border-radius: 0 5px 0 0;
    -webkit-border-radius: 0 5px 0 0;
    -moz-border-radius: 0 5px 0 0;
}
.genealogy-tree li:first-child::after{
    border-radius: 5px 0 0 0;
    -webkit-border-radius: 5px 0 0 0;
    -moz-border-radius: 5px 0 0 0;
}
.genealogy-tree ul ul::before{
    content: '';
    position: absolute; top: 0; left: 50%;
    border-left: 2px solid #ccc;
    width: 0; height: 20px;
}
.genealogy-tree li a{
    text-decoration: none;
    color: #666;
    font-family: arial, verdana, tahoma;
    font-size: 11px;
    display: inline-block;
    border-radius: 5px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
}

.genealogy-tree li a:hover+ul li::after, 
.genealogy-tree li a:hover+ul li::before, 
.genealogy-tree li a:hover+ul::before, 
.genealogy-tree li a:hover+ul ul::before{
    border-color:  #fbba00;
}

/*--------------memeber-card-design----------*/
.member-view-box{
    padding:0px 20px;
    text-align: center;
    border-radius: 4px;
    position: relative;
}
.member-image{
    width: 60px;
    position: relative;
}
.member-image img{
    width: 60px;
    height: 60px;
    border-radius: 6px;
  background-color :#000;
  z-index: 1;
}


</style>