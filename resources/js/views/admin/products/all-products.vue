<template>
  <div class="app-container">
    <div class="filter-container">
      <el-select size="mini" v-model="listQuery.category_id" @change="handleFilter" clearable class="filter-item " style="width:200px;" filterable placeholder="Select Category">
        <el-option v-for="item in categories" :key="item.name" :label="item.name" :value="item.id">
        </el-option>
      </el-select>
      <el-input v-model="listQuery.search" placeholder="Search Records" style="width: 200px;" size="mini" class="filter-item mobile_class" @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" size="mini" icon="el-icon-search" @click="handleFilter">Search</el-button>
      <el-button size="mini" class="filter-item" style="margin-left: 10px;" type="success" @click="handleCreate"><i class="fas fa-plus"></i> Add</el-button>
       <el-select size="mini" v-model="listQuery.is_active" style="width: 140px" clearable placeholder="Products Status" class="filter-item" @change="handleFilter">        
        <el-option  key="1201" label="Active" value="Active" />
        <el-option  key="1202" label="InActive" value="InActive" />
      </el-select>
    </div>
    <el-table :key="tableKey" v-loading="listLoading" :data="list" border fit highlight-current-row style="width: 100%;" @sort-change="sortChange">
      
      <el-table-column
        label="Sr#"
        prop="id"
        sortable="custom"
        align="center"
        width="70"
        :class-name="getSortClass('id')"
      >
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
      
      <el-table-column label="Actions" align="center" width="170" class-name="small-padding">
        <template slot-scope="{row}">
          <el-button circle type="primary" icon="el-icon-edit" @click="handleEdit(row)"></el-button>
          <el-tooltip content="Deactivate" placement="right" effect="dark" v-if="row.is_active==1">
            <el-button icon="el-icon-open" circle type="success" @click="changeProductStatus(row,0)">
            </el-button>
          </el-tooltip>
          <el-tooltip content="Activate" placement="right" effect="dark" v-else>
            <el-button icon="el-icon-turn-off" circle type="info" @click="changeProductStatus(row,1)">
            </el-button>
          </el-tooltip>
        </template>
      </el-table-column>
      <el-table-column label="Name" min-width="200px" align="center">
        <template slot-scope="{row}">
          <span class="link-type" @click="handleEdit(row)">{{ row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Categories" min-width="200px" align="center">
        <template slot-scope="{row}">
          <span v-for="cat in row.categories">{{ cat.name }}, &nbsp;</span>
        </template>
      </el-table-column>
      <el-table-column label="BV" align="center" width="110px">
        <template slot-scope="{row}">
          <span> {{row.pv}} </span>
        </template>
      </el-table-column>
      <el-table-column label="MRP" align="center" width="110px">
        <template slot-scope="{row}">
          <span> {{row.retail_amount}} </span>
        </template>
      </el-table-column>
      <el-table-column label="DP" align="center" width="110px">
        <template slot-scope="{row}">
          <span> {{row.dp_amount}} </span>
        </template>
      </el-table-column>
     
      <el-table-column label="Created at" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.created_at | parseTime('{d}-{m}-{y}') }}</span>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />
  </div>
</template>
<script>
import {
  fetchProducts,
  changeProductStatus,
  getAllCategories
} from "@/api/admin/products-and-categories";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on 
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
      categories:[],
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 5,
        sort: "-id",
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
    getAllCategories().then(response => {
      this.categories = response.data;
    });
  },
  methods: {
    indexMethod(index) {
      let page = this.listQuery.page;
      if (this.listQuery.page == 1) {
        let tempIndex = index * 1;
        let total = this.total + 1;
        return total - (tempIndex + 1);
      } else {

        let tempIndex = this.listQuery.limit * (page - 1) + index;;
        let total = this.total + 1;
        return total - (tempIndex + 1);
      }
    },
    getList() {
      this.listLoading = true;
      fetchProducts(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    handleCreate() {
      this.$router.push({ path: '/products/add' });
    },
    handleEdit(row) {
      this.$router.push({ path: '/products/edit', query: { id: row.id } });
    },

    changeProductStatus(row, status) {
      this.$confirm('Are you sure you want to change status ?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        changeProductStatus(row.id).then((data) => {
          this.dialogCategoryVisible = false;
          this.getList();
          this.$notify({
            title: "Success",
            message: data.message,
            type: "success",
            duration: 2000
          });
        });
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
      return sort === `+${key}` ?
        "ascending" :
        sort === `-${key}` ?
        "descending" :
        "";
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
  .img-upload {
    float: right;
    margin-right: 20px;
  }
}

</style>
