<template>
  <div class="app-container">
    <div class="body genealogy-body genealogy-scroll">
      <div class="genealogy-tree">
        <ul>
          <node :node="members"></node>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import { getAdminGeneology, } from "@/api/members";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import axios from "axios";
import Tinymce from '@/components/Tinymce'
import userImage from '@/assets/images/tree-user.png';
import node from "./components/node";

export default {
  name: "ComplexTable",
  components: { Pagination,Tinymce,node },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        1: "success",
        draft: "info",
        0: "danger"
      };

      return statusMap[status];
    }
  },
  data() {
    return {   
      members:{
        id:0,        
      },   
      temp: {
        title: undefined
      },
    };
  },
  created() {
    this.getGeneology();
  },
  methods: {
    getGeneology(){
      getAdminGeneology().then(response => {
        this.members = response.data;
      });  
    }    
  }
};
</script>

<style scoped>
/*----------------genealogy-scroll----------*/

.app-container{
  padding-top: 0px;
}
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
.genealogy-tree ul ul::before{
    content: '';
    position: absolute; top: 0; left: 50%;
    border-left: 2px solid #ccc;
    width: 0; height: 20px;
}
</style>
