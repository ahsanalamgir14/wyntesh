<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="listQuery.search"
        placeholder="Search Records"
        style="width: 200px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-button
        v-waves
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >Search</el-button>
      <el-button
        class="filter-item"
        style="margin-left: 10px;"
        type="primary"
        icon="el-icon-edit"
        @click="handleCreate"
      >Add</el-button>
      <el-button
        v-waves
        :loading="downloadLoading"
        class="filter-item"
        type="primary"
        icon="el-icon-download"
        @click="handleDownload"
      >Export</el-button>

    </div>

    <el-table
      :key="tableKey"
      v-loading="listLoading"
      :data="list"
      border
      fit
      highlight-current-row
      style="width: 100%;"
      @sort-change="sortChange"
    >
      <el-table-column
        label="ID"
        prop="id"
        sortable="custom"
        align="center"
        width="80"
        :class-name="getSortClass('id')"
      >
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Actions" align="center" width="230" class-name="small-padding">
        <template slot-scope="{row}">

          <el-button
            type="primary"
            icon="el-icon-edit"
            circle
            :loading="buttonLoading"
            @click="handleEdit(row)"
          ></el-button>
          <el-button
            type="danger"
            icon="el-icon-delete"
            circle
            :loading="buttonLoading"
            @click="handleDelete(row)"
          ></el-button>
        </template>
      </el-table-column>
      <el-table-column label="Name" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Default Period" min-width="150px">
        <template slot-scope="{row}">
          <span >{{ row.default_period }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Final Price" min-width="150px">
        <template slot-scope="{row}">
          <span >{{ row.final_price }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Created At" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.created_at | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>
    </el-table>

    <pagination
      v-show="total>0"
      :total="total"
      :page.sync="listQuery.page"
      :limit.sync="listQuery.limit"
      @pagination="getList"
    />
    <el-dialog title="Package Details" width="80%" top="2vh" :visible.sync="dialogPackageVisible">
      <el-tabs type="border-card">
        <el-tab-pane label="Details">
          <el-form ref="dataForm" :rules="rules" :model="temp" label-position="left" label-width="150px" style=" margin-left:50px;">
            <el-row :gutter="20">
              <el-col :span="12">
                <el-form-item label="Name" prop="name">
                  <el-input v-model="temp.name" />
                </el-form-item>

                <el-form-item label="Default Period" prop="default_period">
                  <el-input type="number" min=1 v-model="temp.default_period" />
                </el-form-item>

                <el-form-item label="Price" prop="price">
                  <el-input type="number" min=1 v-model="temp.price" />
                </el-form-item>
                 <el-form-item label="GST (%)" prop="gst">
                  <el-input type="number" @change="calculateFinalPrice()" min=0 v-model="temp.gst" />
                </el-form-item>
                <el-form-item label="GST Amount" prop="gst">
                  <el-input type="number"  min=0 v-model="temp.gst_amount" />
                </el-form-item>
                <el-form-item label="Final Price" prop="final_price">
                  <el-input type="number" min=1 v-model="temp.final_price" />
                </el-form-item>

                <el-form-item label="Description" prop="description">
                  <el-input
                    type="textarea"
                    v-model="temp.description"
                    :rows="2"
                    placeholder="Please Enter description">
                  </el-input>
                </el-form-item>
              </el-col>
              <el-col :span="12">

              </el-col>
            </el-row>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="Courses">
          <el-row style=" height:350px; overflow: auto;">
            <el-checkbox-group v-model="temp.courses" >
              <el-col :span="8" v-for="course in courseList"  :key="course.id">
                <el-checkbox style="margin-top: 10px;" :label="course.id" border :key="course.id">{{course.name}}</el-checkbox>
              </el-col>
            </el-checkbox-group>
          </el-row>
        </el-tab-pane>
      </el-tabs>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogPackageVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" @click="dialogStatus==='create'?createData():updateData()">
          Save
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import {
  fetchList,
  createPackage,
  updatePackage,
  deletePackage,
} from "@/api/packages";
import {
  fetchList as getCourses
} from "@/api/courses";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import axios from "axios";

export default {
  name: "ComplexTable",
  components: { Pagination },
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
      list: null,
      total: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 5,
        name: undefined,
        price: undefined,
        gst:undefined,
        gst_amount:0,
        final_price:undefined,
        courses:[],
        default_period: undefined,
        description: undefined,
        cover_image: undefined,
        sort: "+id"
      },
      courseList:[],
      packageCourses:[],
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        name: undefined,
        courses:[],
        price: undefined,
        gst:undefined,
        gst_amount:0,
        final_price:undefined,
        default_period: undefined,
        description: undefined,
        cover_image: undefined,
      },
      dialogPackageVisible: false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        name: [
          { required: true, message: "Name is required", trigger: "blur" }
        ],
        default_period: [
          { required: true, message: "Default required", trigger: "blur" }
        ],
        price: [
          { required: true, message: "Enter price.", trigger: "blur" }
        ],
        gst: [
          { required: true, message: "Enter GST.", trigger: "blur" }
        ],
        final_price: [
          { required: true, message: "Enter final price.", trigger: "blur" }
        ],

      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
  },
  methods: {
    getList() {
      this.listLoading = true;
      fetchList(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });

      getCourses().then(response => {
        this.courseList = response.data.data;
      });
     
    },
    calculateFinalPrice(){
      console.log(this.temp.gst);
      if(this.temp.gst != undefined && this.temp.gst != null){
        if(this.temp.gst == 0){
          this.temp.gst_amount=0;
          this.temp.final_price=this.temp.price;
        }else{
          let gst=(this.temp.gst*this.temp.price)/100;
          gst=Math.floor(gst);
          this.temp.gst_amount=gst;
          this.temp.final_price=parseInt(this.temp.price)+gst;
        }        
      }
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
        name: undefined,
        courses:[],
        price: undefined,
        gst:0,
        gst_amount:0,
        final_price:undefined,
        default_period: undefined,
        description: undefined,
        cover_image: undefined,
      };
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogPackageVisible = true;
      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });
    },
    createData() {
      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          createPackage(this.temp).then((response) => {
            this.list.unshift(response.data);
            this.dialogPackageVisible = false;
            this.$notify({
              title: "Success",
              message: "Created Successfully",
              type: "success",
              duration: 2000
            })
          }).catch((res)=>{

          });
        }
      });
    },
    handleEdit(row) {
      this.temp = Object.assign({}, row);
      this.dialogStatus = "update";
      this.dialogPackageVisible = true;
      var keys = [];

      row.courses.map(course => {
          keys.push(course.id);
      })

      this.temp.courses=keys;

      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });
    },
    updateData() {
      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          const tempData = Object.assign({}, this.temp);
          updatePackage(tempData,tempData.id).then((response) => {
            for (const v of this.list) {
              if (v.id === this.temp.id) {
                const index = this.list.indexOf(v);
                this.list.splice(index, 1, response.data);
                break;
              }
            }
            this.dialogPackageVisible = false;
            this.$notify({
              title: "Success",
              message: "Update Successfully",
              type: "success",
              duration: 2000
            });
          });
        }
      });
    },
    handleDelete(row) {
      deletePackage(row.id).then((data) => {
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
          "Name",
          "Default Period",
          "Price",
          "GST",
          "Final Price",
          "Created at"
        ];
        const filterVal = [
          "id",
          "name",
          "default_period",
          "price",
          "gst_amount",
          "final_price",
          "created_at"
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "Packages"
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
.edit-input {
  padding-right: 100px;
}
.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}
</style>
