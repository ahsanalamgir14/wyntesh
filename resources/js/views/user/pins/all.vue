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

      <el-select v-model="listQuery.package_id" @change="handleFilter"  clearable class="filter-item" style="width:200px;" filterable placeholder="Select Package">
        <el-option
          v-for="item in packages"
          :key="item.name"
          :label="item.name"
          :value="item.id">
        </el-option>
      </el-select>
      
      <el-select v-model="listQuery.status" @change="handleFilter"  clearable class="filter-item" style="width:200px;" filterable placeholder="Select Status">
        <el-option label="Used" value="Used"></el-option>
        <el-option label="Not Used" value="Not Used"></el-option>
      </el-select>


      <el-button
        v-waves
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >Search</el-button>
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
      <el-table-column label="PIN" width="140px" >
        <template slot-scope="{row}">
          <span>{{ row.pin_number }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Used by" width="130px" >
        <template slot-scope="{row}">
          <span>{{ row.user?row.user.user.username:'' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Package" width="120px" >
        <template slot-scope="{row}">
          <span>{{ row.package.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Base Amount" width="120px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.base_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Tax %" width="120px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.tax_percentage }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Tax Amount" width="120px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.tax_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Total Amount" width="150px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.total_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Status" width="150px" >
        <template slot-scope="{row}">
          <el-tag :type="row.status | statusFilter">{{row.status}}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="Used at" width="150px" align="center">
        <template slot-scope="{row}">
          <span v-if="row.used_at">{{ row.used_at | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>
       <el-table-column label="Allocation date" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.allocated_at | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Note" min-width="150px" >
        <template slot-scope="{row}">
          <span>{{ row.note }}</span>
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
  </div>
</template>

<script>
import { fetchMyPins, deletePinRequest } from "@/api/user/pins";
import { getPackages } from "@/api/user/config";

import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";

export default {
  name: "pin-requests",
  components: { Pagination },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        Approved: "success",
        Pending: "info",
        Rejected: "danger"
      };

      return statusMap[status];
    }
  },
  data() {
    return {
      tableKey: 0,
      list: [],
      total: 0,
      listLoading: false,
      listQuery: {
        page: 1,
        limit: 5,
        search:undefined,
        package_id: undefined,
        status: undefined,
        sort: "+id"
      },
      packages:[],
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        id:undefined,
      },
      
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
    this.getConfig();
  },
  methods: {    
    getList() {
      this.listLoading = true;
      fetchMyPins(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    getConfig() {      
      getPackages().then(response => {
        this.packages = response.data;
      });
    },    
    resetTemp() {
      this.temp = {
        id:undefined,
      };
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";      
      this.$nextTick(() => {
        this.$refs["pinRequestForm"].clearValidate();
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
    getSortClass: function(key) {
      const sort = this.listQuery.sort;
      return sort === `+${key}`
        ? "ascending"
        : sort === `-${key}`
        ? "descending"
        : "";
    },
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "Sr.No",
          "PIN",
          "Used by",
          "Package",
          "Base amount",
          "Tax %",
          "Tax Amount",
          "Total Amount",
          "Status",          
          "Used at",
          "Allocation Date",
          "Note",
        ];
        const filterVal = [
          "id",
          "pin_number",
          "used_by",
          "package_id",
          "base_amount",
          "tax_percentage",
          "tax_amount",
          "total_amount",
          "status",
          "used_at",
          "allocated_at",
          "note",
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "pins"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "timestamp") {
            return parseTime(v[j]);
          } else if(j=="owned_by"){
            return v.owner.user.username
          }else if(j=="package_id"){
            return v.package.name
          }else if(j=="used_by"){
            return v.user?v.user.user.username:'';
          }else {
            return v[j];
          }
        })
      );
    }
  }
};
</script>

<style scoped>

.el-select{
  width:100%;
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
