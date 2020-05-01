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
        icon="el-icon-plus"
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
      <el-table-column label="Actions" align="center" width="150" class-name="small-padding">
        <template slot-scope="{row}">
          <el-button           
            type="primary"
            circle
            icon="el-icon-edit"
            :loading="buttonLoading"            
            @click="handleEdit(row)"
          ></el-button>
          <el-button
            v-role="['superadmin']"
            type="danger"
            icon="el-icon-delete"
            circle
            :loading="buttonLoading"
            @click="handleDelete(row)"
          ></el-button>
          <el-button icon="el-icon-turn-off"
            circle v-if="row.is_active!=1" type="info" @click="handleModifyStatus(row,1)">
          </el-button>
          <el-button icon="el-icon-open" circle v-if="row.is_active!=0" type="success" @click="handleModifyStatus(row,0)">
          </el-button>
        </template>
      </el-table-column>
      <el-table-column label="Name" width="150px">
        <template slot-scope="{row}">
          <span>{{ row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Package Code" width="150px">
        <template slot-scope="{row}">
          <span>{{ row.package_code }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Base Amount" width="150px">
        <template slot-scope="{row}">
          <span >{{ row.base_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="GST Rate" width="150px">
        <template slot-scope="{row}">
          <span >{{ row.gst_rate }}</span>
        </template>
      </el-table-column>
      <el-table-column label="GST Amount" width="150px">
        <template slot-scope="{row}">
          <span >{{ row.gst_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Net Amount" width="150px">
        <template slot-scope="{row}">
          <span >{{ row.net_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Validity" width="150px">
        <template slot-scope="{row}">
          <span >{{ row.validity }}</span>
        </template>
      </el-table-column>      
      <el-table-column label="Capping" width="150px">
        <template slot-scope="{row}">
          <span >{{ row.capping_amount }}</span>
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
    <el-dialog :title="'Package '+textMap[dialogStatus]" width="80%" top="2vh" :visible.sync="dialogPackageVisible">
      <el-tabs type="border-card">
        <el-tab-pane label="Details">
          <el-form ref="packageForm" :rules="rules" :model="temp"  >
            <el-row :gutter="20">
              <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >
                <el-form-item label="Name" prop="name">
                  <el-input v-model="temp.name" />
                </el-form-item>
                <el-form-item label="Package Code" prop="package_code">
                  <el-input v-model="temp.package_code" />
                </el-form-item>
                <el-form-item label="Validity" prop="validity">
                  <el-input type="number" min=0 v-model="temp.validity" />
                </el-form-item>
              </el-col>
              <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >              
                <el-form-item label="Capping Amount" prop="capping_amount">
                  <el-input type="number" min=0 v-model="temp.capping_amount" />
                </el-form-item>
                <el-form-item label="PV" prop="pv">
                  <el-input type="number" min=0 v-model="temp.pv" />
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
              <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >
                <el-form-item label="Base Amount" prop="base_amount">
                  <el-input type="number" min=1 v-model="temp.base_amount" @change="calculateFinalPrice()"/>
                </el-form-item>
                 <el-form-item label="GST (%)" prop="gst_rate">
                  <el-input type="number" @change="calculateFinalPrice()" min=0 v-model="temp.gst_rate" />
                </el-form-item>
                <el-form-item label="GST Amount" prop="gst">
                  <el-input type="number"  min=0 v-model="temp.gst_amount" />
                </el-form-item>
                <el-form-item label="Final Price" prop="net_amount">
                  <el-input type="number" min=1 v-model="temp.net_amount" />
                </el-form-item>
              </el-col>

            </el-row>
          </el-form>
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
  changePackageStatus
} from "@/api/admin/packages";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import axios from "axios";
import role from '@/directive/role'; // Permission directive (v-role)

export default {
  name: "Packages",
  components: { Pagination },
  directives: { waves,role },
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
      list: [],
      total: 3,
      listLoading: false,
      listQuery: {
        page: 1,
        limit: 5,
        search: undefined,
        sort: "+id"
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        name: undefined,
        package_code: undefined,
        description:undefined,
        image:undefined,
        base_amount: undefined,
        gst_rate: undefined,
        gst_amount: undefined,
        net_amount: undefined,
        capping_amount: undefined,
        pv: undefined,
        validity: undefined,
        is_active: 1,
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
        package_code: [
          { required: true, message: "Package code is required", trigger: "blur" }
        ],
        validity: [
          { required: true, message: "Validity required", trigger: "blur" }
        ],
        base_amount: [
          { required: true, message: "Enter base amount.", trigger: "blur" }
        ],
        gst_rate: [
          { required: true, message: "Enter GST Percent.", trigger: "blur" }
        ],
        gst_amount: [
          { required: true, message: "GST amount is required.", trigger: "blur" }
        ],
        net_amount: [
          { required: true, message: "Net amount is required.", trigger: "blur" }
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
     
    },
    calculateFinalPrice(){
      if(this.temp.gst_rate != undefined && this.temp.gst_rate != null){
        if(this.temp.gst_rate == 0){
          this.temp.gst_amount=0;
          this.temp.net_amount=this.temp.base_amount;
        }else{
          let gst=(this.temp.gst_rate*this.temp.base_amount)/100;
          gst=Math.floor(gst);
          this.temp.gst_amount=gst;
          this.temp.net_amount=parseInt(this.temp.base_amount)+gst;
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
        package_code: undefined,
        description:undefined,
        image:undefined,
        base_amount: undefined,
        gst_rate: undefined,
        gst_amount: undefined,
        net_amount: undefined,
        capping_amount: undefined,
        pv: undefined,
        validity: undefined,
        is_active: 1,
      };
    },
     handleModifyStatus(row, status) {
      let data={'id':row.id,'is_active':status};
      changePackageStatus(data).then((response) => {
        this.$notify({
          title: "Success",
          message: response.message,
          type: "success",
          duration: 2000
        })
      })

      row.is_active = status;
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogPackageVisible = true;
      this.$nextTick(() => {
        this.$refs["packageForm"].clearValidate();
      });
    },
    createData() {
      this.$refs["packageForm"].validate(valid => {
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
   
      this.$nextTick(() => {
        this.$refs["packageForm"].clearValidate();
      });
    },
    updateData() {
      this.$refs["packageForm"].validate(valid => {
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
          "Package Code",
          "Base Amount",
          "GST %",
          "GST Amount",
          "Net Amount",
          "Capping Amount",
          "PV",
          "Validity",
          "Active?",
          "Created at"
        ];
        const filterVal = [
          "id",
          "name",
          "package_code",
          "base_amount",
          "gst_rate",
          "gst_amount",
          "net_amount",
          "capping_amount",
          "pv",
          "validity",
          "is_active",
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
