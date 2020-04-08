<template>
  <div class="app-container">
    <div class="tree align-center">
      <ul>
        <li>
          <a href="#" tabindex="0" >
            <img src="@/assets/images/tree-user.png" class="rounded-circle avatarImg" alt="12345678">
            <div class="business-tree-user-info">
              <p class="mb-0 font-weight-bold">Main ID</p>
            </div>
          </a>
          <div>
            <el-tag
              type="primary"
              effect="dark">
              12546565
            </el-tag>
          </div>

          <ul>
            <li>
              <a href="#" >
                <img src="@/assets/images/tree-user.png" class="rounded-circle avatarImg" alt="60208647">
                <div class="business-tree-user-info">
                  <p class="mb-0 font-weight-bold">Child 1</p>
                </div>
              </a>
              <div>
                <el-tag
                  type="primary"
                  effect="dark">
                  12546565
                </el-tag>
              </div>
            </li>
            <li>
              <a >
                <img src="@/assets/images/tree-user.png" class="rounded-circle avatarImg" alt="73433452">
                  <div class="business-tree-user-info">
                    <p class="mb-0 font-weight-bold">Child 2</p>
                  </div>
              </a>
              <div>
                <el-tag
                  type="primary"
                  effect="dark">
                  12546565
                </el-tag>
              </div>
            </li>
             <li>
              <a href="#" >
                <img src="@/assets/images/tree-user.png" class="rounded-circle avatarImg" alt="60208647">
                <div class="business-tree-user-info">
                  <p class="mb-0 font-weight-bold">Child 3</p>
                </div>
              </a>
              <div>
                <el-tag
                  type="primary"
                  effect="dark">
                  12546565
                </el-tag>
              </div>
            </li>
            <li>
              <a >
                <img src="@/assets/images/tree-user.png" class="rounded-circle avatarImg" alt="73433452">
                  <div class="business-tree-user-info">
                    <p class="mb-0 font-weight-bold">Child 4</p>
                  </div>
              </a>
              <div>
                <el-tag
                  type="primary"
                  effect="dark">
                  12546565
                </el-tag>
              </div>
            </li>

          </ul>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import {
  fetchList,
  fetchAchiever,
  deleteAchiever,
  createAchiever,
  updateAchiever
} from "@/api/achievers";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import axios from "axios";
import Tinymce from '@/components/Tinymce'
import userImage from '@/assets/images/tree-user.png';


export default {
  name: "ComplexTable",
  components: { Pagination,Tinymce },
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
      tableKey: 0,
      list: [
        {
          id:1,
          member:{
            code:'1000006',
            name:'Abhishek Raj'
          },
          gross_amount:50000,
          tds:5000,
          admin_charge:1000,
          payable_amount:44000,
          type:'Credit'
        },
        {
          id:2,
          member:{
            code:'1000006',
            name:'Abhishek Raj'
          },
          gross_amount:5000,
          tds:500,
          admin_charge:100,
          payable_amount:4400,
          type:'Debit'
        },
        {
          id:3,
          member:{
            code:'1000007',
            name:'Dhaval Patel'
          },
          gross_amount:60000,
          tds:6000,
          admin_charge:1000,
          payable_amount:53000,
          type:'Credit'
        }
      ],
      total: 2,
      listLoading: false,
      listQuery: {
        page: 1,
        limit: 5,
        title: undefined,
        subtitle: undefined,
        description:undefined,
        date: undefined,
        image:undefined,
        is_visible: 0,
        sort: "+id"
      },
      fileList:[],
      file:undefined,
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        title: undefined,
        subtitle: undefined,
        description:undefined,
        date: undefined,
        is_visible: false,
        image:undefined
      },

      dialogAchieverVisible:false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        title: [{ required: true, message: 'title is required', trigger: 'blur' }],
        date: [{  required: true, message: 'Date is required', trigger: 'blur' }]
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    //this.getList();
  },
  methods: {
    handleChange(f, fl){     
      if(fl.length > 1){
        fl.shift()  
      }      
      this.file=f.raw      
    },
    handleExceed(files, fileList) {
      this.$message.warning(`You can not select more than one file, please remove first.`);
    },
    getList() {
      this.listLoading = true;
      fetchList(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
    sortChange(data) {
      const { prop, order } = data;
      if (prop === "id") {
        this.sortByID(order);
      }
    },
    sortByID(order) {
      if (order === "ascending") {
        this.listQuery.sort = "+id";
      } else {
        this.listQuery.sort = "-id";
      }
      this.handleFilter();
    },
    resetTemp() {
      this.temp = {
        title: undefined,
        subtitle: undefined,
        description:undefined,
        date: undefined,
        is_visible: false,
        image:undefined
      };
      this.file=undefined
      this.fileList=[];
    },
    handleCreate() {
      this.fileList=[];
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogAchieverVisible = true;
      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });
    },
    createData() {
      this.buttonLoading=true;
      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          var form = new FormData();
          let form_data=this.temp;

          for ( var key in form_data ) {
            if(form_data[key] !== undefined && form_data[key] !== null){
              form.append(key, form_data[key]);
            }
          }

          form.append('image', this.file);

          createAchiever(form).then((data) => {
            this.list.unshift(data.data);
            this.dialogAchieverVisible = false;
            this.$notify({
              title: "Success",
              message: "Created Successfully",
              type: "success",
              duration: 2000
            });
            this.buttonLoading=false;
            this.resetTemp();
          });
        }
      });
      this.buttonLoading=false;
    },
    handleEdit(row) {
      this.fileList=[];
      this.file=undefined;
      this.temp = Object.assign({}, row); // copy obj
      if(row.is_visible==1){
        this.temp.is_visible=true
      }else{
        this.temp.is_visible=false
      }
      this.dialogStatus = "update";
      this.dialogAchieverVisible = true;
      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });
    },
    updateData() {
      this.buttonLoading=true;
      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          var form = new FormData();
          const tempData = Object.assign({}, this.temp);

          for ( var key in tempData ) {
            if(tempData[key] !== undefined && tempData[key] !== null){
              form.append(key, tempData[key]);
            }
          }

          form.append('image', this.file);

          
          updateAchiever(form).then((data) => {
            for (const v of this.list) {
              if (v.id === this.temp.id) {
                const index = this.list.indexOf(v);
                this.list.splice(index, 1, data.data);
                break;
              }
            }
            this.dialogAchieverVisible = false;
            this.$notify({
              title: "Success",
              message: "Update Successfully",
              type: "success",
              duration: 2000
            });
            this.buttonLoading=false;
            this.resetTemp();
          });
        }
      });
      this.buttonLoading=false;
    },
    deleteData(row) {
        deleteAchiever(row.id).then((data) => {
            this.dialogAchieverVisible = false;
            this.$notify({
                title: "Success",
                message: "Delete Successfully",
                type: "success",
                duration: 2000
            });
            const index = this.list.indexOf(row);
            this.list.splice(index, 1);
        });
    },
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "ID",
          "Title",
          "Subtitle",
          "Date",
          "Created at"
        ];
        const filterVal = [
          "id",
          "title",
          "subtitle",
          "date",
          "created_at"
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "achievers"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "timestamp") {
            return parseTime(v[j]);
          } else {
            return v[j];
          }
        })
      );
    },
    getSortClass: function(key) {
      const sort = this.listQuery.sort;
      return sort === `+${key}`
        ? "ascending"
        : sort === `-${key}`
        ? "descending"
        : "";
    }
  }
};
</script>

<style scoped>
.tree ul {
    padding: 15px 2px 0 2px;
    position: relative;

    transition: all 0.5s;
    -webkit-transition: all 0.5s;
    -moz-transition: all 0.5s;
}

.tree li {
    float: left; text-align: center;
    list-style-type: none;
    position: relative;
    padding: 15px 2px 0 2px;

    transition: all 0.5s;
    -webkit-transition: all 0.5s;
    -moz-transition: all 0.5s;
}
.tree li img.avatarImg {
    width: 64px;
}
/*We will use ::before and ::after to draw the connectors*/

.tree li::before, .tree li::after{
    content: '';
    position: absolute;
    top: 0;
    right: 50%;
    border-top: 3px solid #3498DB;
    width: 50%;
    height: 20px;
}
.tree li::after{
    right: auto; left: 50%;
    border-left: 1px solid #3498DB;
}

/*We need to remove left-right connectors from elements without
any siblings*/
.tree li:only-child::after, .tree li:only-child::before {
    display: none;
}

/*Remove space from the top of single children*/
.tree li:only-child{ padding-top: 0;}

/*Remove left connector from first child and
right connector from last child*/
.tree li:first-child::before, .tree li:last-child::after{
    border: 0 none;
}
/*Adding back the vertical connector to the last nodes*/
.tree li:last-child::before{
    border-right: 1px solid #3498DB;
    border-radius: 0 5px 0 0;
    -webkit-border-radius: 0 5px 0 0;
    -moz-border-radius: 0 5px 0 0;
}
.tree li:first-child::after{
    border-radius: 5px 0 0 0;
    -webkit-border-radius: 5px 0 0 0;
    -moz-border-radius: 5px 0 0 0;
}

/*Time to add downward connectors from parents*/
.tree ul ul::before{
    content: '';
    position: absolute; top: -6px; left: 50%;
    border-left: 1px solid #3498DB;
    width: 0; height: 23px;
}

.tree li a{
    /*border: 1px solid #3498DB;*/
    padding: 5px 10px;
    text-decoration: none;
    color: #666;
    font-size: 11px;
    display: inline-block;

    border-radius: 5px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;

    transition: all 0.5s;
    -webkit-transition: all 0.5s;
    -moz-transition: all 0.5s;
}

/*Time for some hover effects*/
/*We will apply the hover effect the the lineage of the element also*/
.tree li a:hover, .tree li a:hover+ul li a {
    background: #c8e4f8; color: #000; border: 1px solid #94a0b4;
}
/*Connector styles on hover*/
.tree li a:hover+ul li::after,
.tree li a:hover+ul li::before,
.tree li a:hover+ul::before,
.tree li a:hover+ul ul::before{
    border-color:  #3498DB;
}
.align-center {
  width: 50%;
    margin: 0 auto;
}

.el-drawer__body {
  padding: 20px;
}
.pagination-container {
  margin-top: 5px;
}
.pagination-container {
  background: #fff;
  padding: 15px 16px;
}
@media (min-width:750px) {
  .img-upload{
    float: right;
    margin-right:20px; 
  }
}
</style>
