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
        type="success"
        @click="handleCreate"
      ><i class="fas fa-plus"></i> Add</el-button>
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
      <el-table-column label="Actions" align="center" width="200" class-name="small-padding">
        <template slot-scope="{row}">
          <el-button
            type="primary"
            :loading="buttonLoading"
            circle
            icon="el-icon-edit"
            @click="handleEdit(row)"
          ></el-button>
          <el-button
              circle
              type="danger"
              icon="el-icon-delete"
              @click="deleteData(row)"
          ></el-button>
        </template>
      </el-table-column>
      <el-table-column label="Name" min-width="150px">
        <template slot-scope="{row}">
          <span class="link-type" @click="handleEdit(row)">{{ row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Type" min-width="150px">
        <template slot-scope="{row}">
          <span >{{ row.type }}</span>
        </template>
      </el-table-column>

       <el-table-column label="Created at" width="150px" align="center">
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

    <el-dialog :title="textMap[dialogStatus]" width="60%" top="30px"  :visible.sync="dialogStatusVisible">
      <el-form ref="statusForm" :rules="rules" :model="temp" style="">
        <el-row>
          <el-col  :xs="24" :sm="12" :md="16" :lg="16" :xl="16" >
            <el-form-item label="Name" prop="name">
              <el-input v-model="temp.name" />
            </el-form-item>
             <el-form-item label="Type" prop="type">
              <el-input v-model="temp.type" />
            </el-form-item>
          </el-col>

        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogStatusVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="dialogStatus==='create'?createData():updateData()">
          Create
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import {
  fetchList,
  fetchStatus,
  deleteStatus,
  createStatus,
  updateStatus
} from "@/api/superadmin/statuses";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import axios from "axios";

export default {
  name: "Statuss",
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
        search: undefined,
        sort: "+id"
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        name: undefined,
        type: undefined,
      },

      dialogStatusVisible:false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        type: [{ required: true, message: 'Type is required', trigger: 'blur' }]
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
        id: undefined,
        name: undefined,
        type: undefined,
      };
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogStatusVisible = true;
      this.$nextTick(() => {
        this.$refs["statusForm"].clearValidate();
      });
    },
    createData() {
      
      this.$refs["statusForm"].validate(valid => {
        if (valid) {
          this.buttonLoading=true;
          createStatus(this.temp).then((data) => {
            this.list.unshift(data.data);
            this.dialogStatusVisible = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading=false;
            this.resetTemp();
          }).catch((err)=>{
            this.buttonLoading=false;
          });
        }
      });
    },
    handleEdit(row) {
     
      this.temp = Object.assign({}, row); // copy obj
     
      this.dialogStatus = "update";
      this.dialogStatusVisible = true;
      this.$nextTick(() => {
        this.$refs["statusForm"].clearValidate();
      });
    },
    updateData() {
      this.$refs["statusForm"].validate(valid => {
        if (valid) {         
          const tempData = Object.assign({}, this.temp);
          this.buttonLoading=true;
          updateStatus(tempData).then((data) => {
            for (const v of this.list) {
              if (v.id === this.temp.id) {
                const index = this.list.indexOf(v);
                this.list.splice(index, 1, data.data);
                break;
              }
            }
            this.dialogStatusVisible = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading=false;
            this.resetTemp();
          }).catch((err)=>{
            this.buttonLoading=false;
          });
        }
      });
    },
    deleteData(row) {
        this.buttonLoading=true;
        deleteStatus(row.id).then((data) => {
            this.dialogStatusVisible = false;
            this.$notify({
                title: "Success",
                message: data.message,
                type: "success",
                duration: 2000
            });
            this.buttonLoading=false;
            const index = this.list.indexOf(row);
            this.list.splice(index, 1);
        }).catch((err)=>{
          this.buttonLoading=false;
        });
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
@media (min-width:750px) {
  .img-upload{
    float: right;
    margin-right:20px; 
  }
}
</style>
