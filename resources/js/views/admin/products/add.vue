<template>
  <div class="app-container">
    <el-form ref="productForm" :rules="rules" :model="temp" label-position="top" style="">
      <el-tabs type="border-card" v-loading="loading">
        <el-tab-pane label="Product Details">
          <el-row :gutter="20">
            <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
              <el-form-item label="Name" prop="name">
                <el-input v-model="temp.name" />
              </el-form-item>
              <el-form-item label="Product Code" prop="product_number">
                <el-input v-model="temp.product_number" :disabled="is_updating" />
              </el-form-item>
              <el-form-item label="Brand Name" prop="brand_name">
                <el-input v-model="temp.brand_name" />
              </el-form-item>
              <el-form-item label="HSN" prop="hsn">
                <el-input v-model="temp.hsn" />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
              <el-form-item label="Categories" prop="parent_id">
                <el-select v-model="temp.categories" multiple clearable style="width:100%;" filterable placeholder="Select Categories">
                  <el-option v-for="item in categories" :key="item.id" :label="item.name" :value="item.id">
                  </el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="PV" prop="pv">
                <el-input type="number" min="0" v-model="temp.pv" />
              </el-form-item>
              <el-form-item label="Quantity" prop="qty">
                <el-input type="number" min="1" v-model="temp.qty" />
              </el-form-item>
              <el-form-item label="Quantity Unit" prop="qty_unit">
                <el-input v-model="temp.qty_unit" />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
              <!-- <el-form-item label="Discount Rate" prop="discount_rate">
                <el-input type="number" min="0" v-model="temp.discount_rate" />
              </el-form-item> -->
              <el-form-item label="Priority" prop="priority">
                <el-input type="number" min="0" v-model="temp.priority" />
              </el-form-item>
              <el-form-item label="Color variant available ?" prop="is_color_variant">
                <el-checkbox size="mini" v-model="temp.is_color_variant" label="Yes/No" prop="is_color_variant" border></el-checkbox>
              </el-form-item>
              <el-form-item label="Size variant available ?" prop="discount_amount">
                <el-checkbox size="mini" v-model="temp.is_size_variant" label="Yes/No" prop="is_size_variant" border></el-checkbox>
              </el-form-item>
              <el-form-item size="mini" label="Waive Shipping ?" prop="is_shipping_waiver">
                <el-checkbox v-model="temp.is_shipping_waiver" border>Waive Shipping ?</el-checkbox>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6">
              <div class="img-upload">
                <el-form-item prop="cover_image" style="float: left;margin-right: 40px;margin-top:10px;">
                  <label for="Cover Image" style="line-height: 2;"> Cover Image</label>
                  <el-upload class="avatar-uploader" action="#" ref="upload" :show-file-list="true" :auto-upload="false" :on-change="handleChange" :on-remove="handleRemove" :limit="1" :file-list="fileList" :on-exceed="handleExceed" accept="image/png, image/jpeg">
                    <img v-if="temp.cover_image" :src="temp?temp.cover_image_thumbnail:''" class="avatar">
                    <i v-if="temp.cover_image" slot="default" class="el-icon-plus"></i>
                    <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                  </el-upload>
                  <a v-if="temp.cover_image" :href="temp?temp.cover_image:''" target="_blank">View full image.</a>
                </el-form-item>
              </div>
            </el-col>
          </el-row>
          <el-row>
            <hr>
            <div style="float: right;margin-top:10px;">
              <el-button type="success" icon="el-icon-finished" :loading="buttonLoading" size="mini" @click="is_updating?updateProduct():addProduct()">Save</el-button>
            </div>
          </el-row>
        </el-tab-pane>
        <el-tab-pane label="Taxes">
          <el-row :gutter="20">
            <el-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8">
              <el-row :gutter="15">
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                  <el-form-item label="Cost Base" prop="cost_base">
                    <el-input type="number" size="mini" min="1" v-model="temp.cost_base" />
                  </el-form-item>
                  <el-form-item label="Cost GST Rate" prop="cost_gst_rate">
                    <el-input type="number" size="mini" min="1" v-model="temp.cost_gst_rate" @change="calculateFinalCostPrice" />
                  </el-form-item>
                  <el-form-item label="Cost GST Amount" prop="cost_gst_amount">
                    <el-input type="number" size="mini" min="1" v-model="temp.cost_gst_amount" />
                  </el-form-item>
                  <el-form-item label="Cost CGST Rate" prop="cost_cgst_rate">
                    <el-input type="number" size="mini" min="1" v-model="temp.cost_cgst_rate" />
                  </el-form-item>
                  <el-form-item label="Cost CGST Amount" prop="cost_cgst_amount">
                    <el-input type="number" size="mini" min="1" v-model="temp.cost_cgst_amount" />
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                  <el-form-item label="Cost SGST Rate" prop="cost_sgst_rate">
                    <el-input type="number" size="mini" min="1" v-model="temp.cost_sgst_rate" />
                  </el-form-item>
                  <el-form-item label="Cost SGST Amount" prop="cost_sgst_amount">
                    <el-input type="number" size="mini" min="1" v-model="temp.cost_sgst_amount" />
                  </el-form-item>
                  <el-form-item label="Cost UTGST Rate" prop="cost_utgst_rate">
                    <el-input type="number" size="mini" min="1" v-model="temp.cost_utgst_rate" />
                  </el-form-item>
                  <el-form-item label="Cost UTGST Amount" prop="cost_utgst_amount">
                    <el-input type="number" size="mini" min="1" v-model="temp.cost_utgst_amount" />
                  </el-form-item>
                  <el-form-item label="Cost Amount" prop="cost_amount">
                    <el-input type="number" size="mini" min="1" v-model="temp.cost_amount" />
                  </el-form-item>
                </el-col>
              </el-row>
            </el-col>
            <el-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8">
              <el-row :gutter="15">
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                  <el-form-item label="DP Base" prop="dp_base">
                    <el-input type="number" size="mini" min="1" v-model="temp.dp_base" />
                  </el-form-item>
                  <el-form-item label="DP GST Rate" prop="dp_gst_rate">
                    <el-input type="number" size="mini" min="1" v-model="temp.dp_gst_rate" @change="calculateFinalDPPrice"/>
                  </el-form-item>
                  <el-form-item label="DP GST Amount" prop="dp_gst_amount">
                    <el-input type="number" size="mini" min="1" v-model="temp.dp_gst_amount" />
                  </el-form-item>
                  <el-form-item label="DP CGST Rate" prop="dp_cgst_rate">
                    <el-input type="number" size="mini" min="1" v-model="temp.dp_cgst_rate" />
                  </el-form-item>
                  <el-form-item label="DP CGST Amount" prop="dp_cgst_amount">
                    <el-input type="number" size="mini" min="1" v-model="temp.dp_cgst_amount" />
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                  <el-form-item label="DP SGST Rate" prop="dp_sgst_rate">
                    <el-input type="number" size="mini" min="1" v-model="temp.dp_sgst_rate" />
                  </el-form-item>
                  <el-form-item label="DP SGST Amount" prop="dp_sgst_amount">
                    <el-input type="number" size="mini" min="1" v-model="temp.dp_sgst_amount" />
                  </el-form-item>
                  <el-form-item label="DP UTGST Rate" prop="dp_utgst_rate">
                    <el-input type="number" size="mini" min="1" v-model="temp.dp_utgst_rate" />
                  </el-form-item>
                  <el-form-item label="DP UTGST Amount" prop="dp_utgst_amount">
                    <el-input type="number" size="mini" min="1" v-model="temp.dp_utgst_amount" />
                  </el-form-item>
                  <el-form-item label="DP Amount" prop="dp_amount">
                    <el-input type="number" size="mini" min="1" v-model="temp.dp_amount" />
                  </el-form-item>
                </el-col>
              </el-row>
            </el-col>
            <el-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8">
              <el-row :gutter="15">
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                  <el-form-item label="Retail Base" prop="retail_base">
                    <el-input type="number" size="mini" min="1" v-model="temp.retail_base" />
                  </el-form-item>
                  <el-form-item label="Retail GST Rate" prop="retail_gst_rate">
                    <el-input type="number" size="mini" min="1" v-model="temp.retail_gst_rate" @change="calculateFinalRetailPrice"/>
                  </el-form-item>
                  <el-form-item label="Retail GST Amount" prop="retail_gst_amount">
                    <el-input type="number" size="mini" min="1" v-model="temp.retail_gst_amount" />
                  </el-form-item>
                  <el-form-item label="Retail CGST Rate" prop="retail_cgst_rate">
                    <el-input type="number" size="mini" min="1" v-model="temp.retail_cgst_rate" />
                  </el-form-item>
                  <el-form-item label="Retail CGST Amount" prop="retail_cgst_amount">
                    <el-input type="number" size="mini" min="1" v-model="temp.retail_cgst_amount" />
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                  <el-form-item label="Retail SGST Rate" prop="retail_sgst_rate">
                    <el-input type="number" size="mini" min="1" v-model="temp.retail_sgst_rate" />
                  </el-form-item>
                  <el-form-item label="Retail SGST Amount" prop="retail_sgst_amount">
                    <el-input type="number" size="mini" min="1" v-model="temp.retail_sgst_amount" />
                  </el-form-item>
                  <el-form-item label="Retail UTGST Rate" prop="retail_utgst_rate">
                    <el-input type="number" size="mini" min="1" v-model="temp.retail_utgst_rate" />
                  </el-form-item>
                  <el-form-item label="Retail UTGST Amount" prop="retail_utgst_amount">
                    <el-input type="number" size="mini" min="1" v-model="temp.retail_utgst_amount" />
                  </el-form-item>
                  <el-form-item label="Retail Amount" prop="retail_amount">
                    <el-input type="number" size="mini" min="1" v-model="temp.retail_amount" />
                  </el-form-item>
                </el-col>
              </el-row>
            </el-col>
          </el-row>
          <el-row>
            <hr>
            <div style="float: right;margin-top:10px;">
              <el-button type="success" size="mini" icon="el-icon-finished" :loading="buttonLoading" @click="is_updating?updateProduct():addProduct()">Save</el-button>
            </div>
          </el-row>
        </el-tab-pane>
        <el-tab-pane label="Details and Images">
          <el-row :gutter="50">
            <el-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14">
              <el-form-item label="Description" prop="description">
                <tinymce v-model="temp.description" :imageUploadButton="false" menubar="format" :toolbar="tools" id="productDescription" ref="productDescription" :value="temp.description" :height="50" />
              </el-form-item>
              <el-form-item label="Benefits" prop="benefits">
                <tinymce v-model="temp.benefits" :imageUploadButton="false" menubar="format" :toolbar="tools" id="productBenefits" ref="productBenefits" :value="temp.benefits" :height="50" />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10" v-if="is_updating">
              <div class="filter-container" style="margin-top: 10px;">
                <span style="font-size: 14px; font-weight: bold; color: #606266;">Product Images</span>
                <el-button class="filter-item" style="margin-left: 10px;float: right;" size="mini" type="success" @click="handleAddImage()"><i class="fas fa-plus"></i> Add</el-button>
              </div>
              <el-table :data="temp.images" style="width: 100%">
                <el-table-column label="Variant" width="220px" align="left">
                  <template slot-scope="{row}">
                    <span>{{ row.variant?row.variant.color?row.variant.color.name:'':'' }}-{{ row.variant?row.variant.size?row.variant.size.name:'':'' }}</span>
                  </template>
                </el-table-column>
                <el-table-column label="Image" width="120px" align="left">
                  <template slot-scope="{row}">
                    <span><a v-if="row.url" :href="row.url" target="_blank">View image.</a></span>
                  </template>
                </el-table-column>
                <el-table-column label="Actions" align="center" width="200" class-name="small-padding">
                  <template slot-scope="{row}">
                    <el-button circle type="danger" :loading="buttonLoading" icon="el-icon-delete" @click="deleteImage(row)"></el-button>
                  </template>
                </el-table-column>
              </el-table>
            </el-col>
          </el-row>
          <el-row>
            <hr>
            <div style="float: right;margin-top:10px;">
              <el-button type="success" size="mini" icon="el-icon-finished" :loading="buttonLoading" @click="is_updating?updateProduct():addProduct()">Save</el-button>
            </div>
          </el-row>
        </el-tab-pane>
        <el-tab-pane ab-pane label="Product Variant" v-if="is_updating">
          <el-row :gutter="50">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="is_updating">
              <div class="filter-container" style="margin-top: 10px;">
                <el-button class="filter-item" style="margin-left: 10px;float: right;" size="mini" type="success" @click="handleAddVariant()"><i class="fas fa-plus"></i> Add</el-button>
              </div>
              <el-table :key="tableKey" :data="all_variants" border fit highlight-current-row style="width: 100%;">
                <el-table-column label="Activate / Deactivate" align="center" width="170px" class-name="small-padding">
                  <template slot-scope="{row}">
                    <el-tooltip content="Deactivate" placement="right" effect="dark" v-if="row.is_active==1">
                      <el-button icon="el-icon-open" circle type="success" @click="changeVariantStatus(row,0)">
                      </el-button>
                    </el-tooltip>
                    <el-tooltip content="Activate" placement="right" effect="dark" v-else>
                      <el-button icon="el-icon-turn-off" circle type="info" @click="changeVariantStatus(row,1)">
                      </el-button>
                    </el-tooltip>
                  </template>
                </el-table-column>
                <el-table-column label="SKU" min-width="100px" align="center">
                  <template slot-scope="{row}">
                    <span>{{ row.sku_code }}</span>
                  </template>
                </el-table-column>
                <el-table-column label="Stock" align="center" width="120px">
                  <template slot-scope="{row}">
                    <span> {{row.stock}} </span>
                  </template>
                </el-table-column>
                <el-table-column label="Color" align="center" width="120px">
                  <template slot-scope="{row}">
                    <span> {{row.color?row.color.name:''}} </span>
                  </template>
                </el-table-column>
                <el-table-column label="Size" align="center" width="120px">
                  <template slot-scope="{row}">
                    <span> {{row.size?row.size.name:''}} </span>
                  </template>
                </el-table-column>
              </el-table>
            </el-col>
          </el-row>
        </el-tab-pane>
        
      </el-tabs>
    </el-form>
    <el-dialog title="Add Image" width="60%" top="30px" :fullscreen="is_mobile" :visible.sync="dialogAddImageVisible">
      <el-form ref="imageForm" style="">
        <el-row :gutter="20">
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <el-form-item label="Select Variant (SKU)" prop="variant">
              <el-select v-model="image.variant" clearable style="width:100%;" filterable placeholder="Select Sku">
                <el-option v-for="item in all_variants" :key="item.id" :label="item.sku_code" :value="item.id">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="Order" prop="order">
              <el-input type="number" min="1" v-model="image.order" />
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <el-form-item label="Is Fetured" prop="is_fetured">
              <br>
              <el-select v-model="image.is_fetured" style="width: 100%" placeholder="Select">
                <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item prop="cover_image" style="margin-right: 40px;">
              <label for="Select Image"> Select Image</label>
              <el-upload class="avatar-uploader" action="#" ref="productImage" :show-file-list="true" :auto-upload="false" :on-change="handleImageChange" :on-remove="handleImageRemove" :limit="1" :file-list="imageFileList" :on-exceed="handleImageExceed" accept="image/png, image/jpeg">
                <i class="el-icon-plus avatar-uploader-icon"></i>
              </el-upload>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button size="mini" @click="dialogAddImageVisible = false">
          Cancel
        </el-button>
        <el-button size="mini" type="primary" :loading="buttonLoading" @click="addImage()">
          Upload
        </el-button>
      </div>
    </el-dialog>
    <el-dialog :title="title" width="50%" top="30px" :fullscreen="is_mobile" :visible.sync="dialogAddVariantVisible">
      <el-form ref="variantForm" :rules="skuRules" :model="product_variant" style="">
        <el-row :gutter="20">
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <el-form-item label="SKU" prop="sku">
              <el-input disabled min="1" v-model="product_variant.sku" />
            </el-form-item>
            <el-form-item label="Current Stock" prop="stock">
              <el-input disabled type="number" min="1" v-model="product_variant.stock" />
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <el-form-item v-if="this.temp.is_color_variant" label="Color Variant" prop="color_variant">
              <el-select @change="getColorVariantInfo" v-model="product_variant.color_variant" clearable style="width:100%;" filterable placeholder="Color Variant">
                <el-option v-for="item in color_variants" :key="item.id" :label="item.name" :value="item.id">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item v-if="this.temp.is_size_variant" label="Size Variant" prop="size_variant">
              <el-select @change="getSizeVariantInfo" v-model="product_variant.size_variant" clearable style="width:100%;" filterable placeholder="Size Variant">
                <el-option v-for="item in size_variants" :key="item.id" :label="item.name" :value="item.id">
                </el-option>
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogAddVariantVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" :disabled="disabledVariant" :loading="buttonLoading" @click="addVariant()">
          {{dialogStatus==='create'?"Add":"Update" }}
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
import {
  getAllCategories,
  getAllProductVariant,
  getAllColorVariant,
  getAllSizeVariant,
  getAllProductVariantList,
  createProduct,
  getProduct,
  updateProduct,
  deleteProductImage,
  uploadProductImage,
  changeVariantStatus,
  addProductVariant,
} from "@/api/admin/products-and-categories";

import Tinymce from '@/components/Tinymce'
import waves from "@/directive/waves";
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";

export default {
  name: "Products",
  components: { Pagination, Tinymce },
  directives: { waves },
  data() {
    return {
      product_code_prefix: "SKU",
      is_mobile: false,
      list: null,
      listLoading:false,
      listUnitFactors: [],
      total: 0,
      tableKey: 0,
      listQuery: {
        page: 1,
        limit: 5,
        is_active: 0,
        sort: "+id"
      },

      options: [{
        value: '1',
        label: 'Yes'
      }, {
        value: '0',
        label: 'No'
      }],
      product_variant: {
        id: undefined,
        color_variant: undefined,
        size_variant: undefined,
        stock: 0,
        product_id: undefined,
        product_edit_id: undefined,
        variant_id: undefined,
        sku: undefined,
      },
      image: {
        variant: undefined,
        order: undefined,
        is_fetured: undefined,
      },
      skuRules: {
        color_variant: [{ required: true, message: "Color is required.", trigger: "blur" }],
        size_variant: [{ required: true, message: "Size is required.", trigger: "blur" }],
        sku: [{ required: true, message: "SKU required.", trigger: "blur" }],
      },
      temp: {
        product_number: undefined,
        name: undefined,
        brand_name: undefined,
        qty: undefined,
        qty_unit: undefined,
        hsn: undefined,
        description: undefined,
        benefits: undefined,
        is_color_variant: false,
        is_size_variant: false,
        is_shipping_waiver: false,
        cost_base: 0,
        cost_gst_rate: 0,
        cost_gst_amount: 0,
        cost_cgst_rate: 0,
        cost_cgst_amount: 0,
        cost_sgst_rate: 0,
        cost_sgst_amount: 0,
        cost_utgst_rate: 0,
        cost_utgst_amount: 0,
        cost_amount: 0,
        dp_base: 0,
        cost_gst: 0,
        dp_gst_amount: 0,
        dp_cgst_rate: 0,
        dp_cgst_amount: 0,
        dp_sgst_rate: 0,
        dp_sgst_amount: 0,
        dp_utgst_rate: 0,
        dp_utgst_amount: 0,
        dp_amount: 0,
        retail_base: 0,
        retail_gst_rate: 0,
        retail_gst_amount: 0,
        retail_cgst_rate: 0,
        retail_cgst_amount: 0,
        retail_sgst_rate: 0,
        retail_sgst_amount: 0,
        retail_utgst_rate: 0,
        retail_utgst_amount: 0,
        retail_amount: 0,
        dp_gst_rate: 0,
        priority: 0,
        dp_gst: undefined,
        retail_gst: undefined,
        discount_rate: undefined,
        discount_amount: undefined,
        pv: undefined,
        cover_image: undefined,
        cover_image_thumbnail: undefined,
        categories: [],
        images: [],
      },
      rules: {
        product_number: [
          { required: true, message: "Product Number is required.", trigger: "blur" }
        ],
        name: [
          { required: true, message: "Product name is required.", trigger: "blur" }
        ],
        brand_name: [
          { required: true, message: "Brand name is required", trigger: "blur" }
        ],
        qty: [
          { required: true, message: "Quantity is required", trigger: "blur" }
        ],
        pv: [
          { required: true, message: "PV is required", trigger: "blur" }
        ],
        qty_unit: [
          { required: true, message: "Quantity unit is required", trigger: "blur" }
        ],
        hsn: [
          { required: true, message: "HSN unit is required", trigger: "blur" }
        ],
        cost_base: [
          { required: true, message: "Base cost is required.", trigger: "blur" }
        ],
        cost_gst_rate: [
          { required: true, message: "Cost GST Rate is required.", trigger: "blur" }
        ],
        cost_gst_amount: [
          { required: true, message: "Cost GST Amount is required.", trigger: "blur" }
        ],
        cost_cgst_rate: [
          { required: true, message: "Cost GST Amount is required.", trigger: "blur" }
        ],
        cost_cgst_amount: [
          { required: true, message: "Cost CGST Amount is required.", trigger: "blur" }
        ],
        cost_sgst_rate: [
          { required: true, message: "Cost SGST Rate is required.", trigger: "blur" }
        ],
        cost_sgst_amount: [
          { required: true, message: "Cost SGST Amount is required.", trigger: "blur" }
        ],
        cost_utgst_rate: [
          { required: true, message: "Cost UTGST Rate is required.", trigger: "blur" }
        ],
        cost_utgst_amount: [
          { required: true, message: "Cost UTGST Amount is required.", trigger: "blur" }
        ],
        cost_amount: [
          { required: true, message: "Cost Amount is required.", trigger: "blur" }
        ],
        cost_gst: [
          { required: true, message: "GST on cost is required.", trigger: "blur" }
        ],
        dp_gst_rate: [
          { required: true, message: "DP GST rate is required", trigger: "blur" }
        ],
        dp_gst_amount: [
          { required: true, message: "DP GST amount is required", trigger: "blur" }
        ],
        dp_cgst_rate: [
          { required: true, message: "DP CGST rate is required", trigger: "blur" }
        ],
        dp_cgst_amount: [
          { required: true, message: "DP CGST amount is required", trigger: "blur" }
        ],
        dp_sgst_rate: [
          { required: true, message: "DP SGST rate is required", trigger: "blur" }
        ],
        dp_sgst_amount: [
          { required: true, message: "DP SGST amount is required", trigger: "blur" }
        ],
        dp_utgst_rate: [
          { required: true, message: "DP UTGST rate is required", trigger: "blur" }
        ],
        dp_utgst_amount: [
          { required: true, message: "DP UTGST amount is required", trigger: "blur" }
        ],
        retail_base: [
          { required: true, message: "DP retail base is required", trigger: "blur" }
        ],
        retail_gst_rate: [
          { required: true, message: "retail GST rate is required", trigger: "blur" }
        ],
        retail_gst_amount: [
          { required: true, message: "retail GST amount is required", trigger: "blur" }
        ],
        retail_cgst_rate: [
          { required: true, message: "retail CGST rate is required", trigger: "blur" }
        ],
        retail_cgst_amount: [
          { required: true, message: "retail CGST amount is required", trigger: "blur" }
        ],
        retail_sgst_rate: [
          { required: true, message: "retail SGST rate is required", trigger: "blur" }
        ],
        retail_sgst_amount: [
          { required: true, message: "retail SGST amount is required", trigger: "blur" }
        ],
        retail_utgst_rate: [
          { required: true, message: "retail UTGST rate is required", trigger: "blur" }
        ],
        retail_utgst_amount: [
          { required: true, message: "retail UTGST amount is required", trigger: "blur" }
        ],
        retail_amount: [
          { required: true, message: "retail amount is required", trigger: "blur" }
        ],
        dp_amount: [
          { required: true, message: "DP amount is required", trigger: "blur" }
        ],
        dp_base: [
          { required: true, message: "DP Base price is required.", trigger: "blur" }
        ],
        dp_gst: [
          { required: true, message: "GST on dp price is required.", trigger: "blur" }
        ],
        retail_gst: [
          { required: true, message: "GST on retail price is required.", trigger: "blur" }
        ],
      },
      unitFactor: {
        product_id: undefined,
        name: undefined,
        description: undefined,
        unit_factor_type_id: undefined,
        value_percent: undefined,
        value_amount: undefined,
      },
      rulesFactor: {
        name: [
          { required: true, message: "Name is required", trigger: "blur" }
        ],
        unit_factor_type_id: [
          { required: true, message: "Type is required", trigger: "blur" }
        ],
        value_percent: [
          { required: true, message: "Value % is required", trigger: "blur" }
        ],
        value_amount: [
          { required: true, message: "Value is required", trigger: "blur" }
        ],
      },
      unitFactorTypes: [],
      dialogFactorsVisible: false,
      dialogFactorsAddVisible: false,
      dialogFactorsTitle: '',
      title: "",
      tools: [''],
      categories: [],
      variants: [],
      all_variants: [],
      color_variants: [],
      size_variants: [],
      fileList: [],
      file: undefined,
      imageFileList: [],
      imageFile: undefined,
      dialogAddImageVisible: false,
      dialogAddVariantVisible: false,
      dialogStatus: "create",
      disabledVariant: false,
      buttonLoading: false,
      loading: false,
      color_variant_code: "",
      size_variant_code: "",
      is_updating: false,
    };
  },
  created() {
    this.getlist();
    this.getAllCats();
    this.getAllProductVariant();
    this.getAllProductVariantList();
    this.getAllColorVariant();
    this.getAllSizeVariant();

    if (window.screen.width <= '550') {
      this.is_mobile = true;
    }
  },
  methods: {
    getlist() {
      let id = this.$route.query.id
      if (id) {
        this.is_updating = true;
        getProduct(id).then((response) => {
          this.temp = response.data;
          this.temp.is_color_variant = response.data.is_color_variant == 1 ? true : false;
          this.temp.is_size_variant = response.data.is_size_variant == 1 ? true : false;
          this.temp.is_shipping_waiver = response.data.is_shipping_waiver == 1 ? true : false;
          var keys = [];
          response.data.categories.map(cat => {
            keys.push(cat.id);
          })
          keys = keys.filter((item, i, ar) => ar.indexOf(item) === i);
          this.temp.categories = keys;
        })
       
      }
    },
    getColorVariantInfo() {
      if (this.product_variant.color_variant) {
        let color_variants = this.color_variants.filter((color_variants) => {
          return this.product_variant.color_variant == color_variants.id;
        })[0];
        this.color_variant_code = color_variants.name;

        if (this.product_code_prefix) {
          this.product_variant.sku = this.product_code_prefix + '_' + this.temp.product_number.toUpperCase().replace(/\s/g, '');
        }
        if (this.color_variant_code) {
          this.product_variant.sku += '_' + this.color_variant_code.toUpperCase().replace(/\s/g, '');
        }
        if (this.size_variant_code) {
          this.product_variant.sku += '_' + this.size_variant_code.toUpperCase().replace(/\s/g, '');
        }

      } else {
        this.handleAddVariant();
      }
    },
    getSizeVariantInfo() {
      if (this.product_variant.size_variant) {
        let size_variants = this.size_variants.filter((size_variants) => {
          return this.product_variant.size_variant == size_variants.id;
        })[0];
        this.size_variant_code = size_variants.brand_size;

        if (this.product_code_prefix) {
          this.product_variant.sku = this.product_code_prefix + '_' + this.temp.product_number.toUpperCase().replace(/\s/g, '');
        }
        if (this.color_variant_code) {
          this.product_variant.sku += '_' + this.color_variant_code.toUpperCase().replace(/\s/g, '');
        }
        if (this.size_variant_code) {
          this.product_variant.sku += '_' + this.size_variant_code.toUpperCase().replace(/\s/g, '');
        }

      } else {
        this.size_variant_code = '';
        this.getColorVariantInfo();
      }
    },
    getAllProductVariant() {      
      getAllProductVariant().then(response => {
        this.variants = response.data;
      });
    },
    getAllProductVariantList() {      
      let id = this.$route.query.id;
      getAllProductVariantList(id).then(response => {
        this.all_variants = response.data;
      });
    },

    getAllColorVariant() {      
      getAllColorVariant().then(response => {
        this.color_variants = response.data;
      });
    },
    getAllSizeVariant() {      
      getAllSizeVariant().then(response => {
        this.size_variants = response.data;
      });
    },
    getAllCats() {      
      getAllCategories().then(response => {
        this.categories = response.data;
      });
    },
    changeVariantStatus(row, status) {
      this.$confirm('Are you sure you want to change status ?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        changeVariantStatus(row.id).then((data) => {
          this.dialogNoticesVisible = false;
          this.getAllProductVariant();
          this.getAllProductVariantList();
          this.$notify({
            title: "Success",
            message: data.message,
            type: "success",
            duration: 2000
          });
        });
      });
    },
    resetProductVariant() {
      this.product_variant = {
        id: undefined,
        color_variant: undefined,
        size_variant: undefined,
        stock: 0,
        product_id: undefined,
        product_edit_id: undefined,
        variant_id: undefined,
        sku: undefined,
      }
    },
    calculateFinalDPPrice() {
      if (this.temp.dp_gst_rate != undefined && this.temp.dp_gst_rate != null) {
        if (this.temp.gst_rate == 0) {
          this.temp.dp_amount = 0;
          this.temp.dp_cgst_rate = 0;
          this.temp.dp_cgst_amount = 0;
          this.temp.dp_sgst_rate = 0;
          this.temp.dp_sgst_amount = 0;
          this.temp.dp_amount = this.temp.dp_base;
        } else {
          let gst = (this.temp.dp_gst_rate * this.temp.dp_base) / 100;
          gst = Math.floor(gst);
          this.temp.dp_gst_amount = gst;
          this.temp.dp_cgst_rate = this.temp.dp_gst_rate/2;
          this.temp.dp_cgst_amount = gst/2;
          this.temp.dp_sgst_rate = this.temp.dp_gst_rate/2;
          this.temp.dp_sgst_amount = gst/2;
          this.temp.dp_amount = parseInt(this.temp.dp_base) + gst;
        }
      }
    },
    calculateFinalRetailPrice() {
      if (this.temp.retail_gst_rate != undefined && this.temp.retail_gst_rate != null) {
        if (this.temp.gst_rate == 0) {
          this.temp.retail_amount = 0;
          this.temp.retail_cgst_rate = 0;
          this.temp.retail_cgst_amount = 0;
          this.temp.retail_sgst_rate = 0;
          this.temp.retail_sgst_amount = 0;
          this.temp.retail_amount = this.temp.retail_base;
        } else {
          let gst = (this.temp.retail_gst_rate * this.temp.retail_base) / 100;
          gst = Math.floor(gst);
          this.temp.retail_gst_amount = gst;
          this.temp.retail_cgst_rate = this.temp.retail_gst_rate/2;
          this.temp.retail_cgst_amount = gst/2;
          this.temp.retail_sgst_rate = this.temp.retail_gst_rate/2;
          this.temp.retail_sgst_amount = gst/2;
          this.temp.retail_amount = parseInt(this.temp.retail_base) + gst;
        }
      }
    },
    calculateFinalCostPrice() {
      if (this.temp.cost_gst_rate != undefined && this.temp.cost_gst_rate != null) {
        if (this.temp.gst_rate == 0) {
          this.temp.cost_amount = 0;
          this.temp.cost_cgst_rate = 0;
          this.temp.cost_cgst_amount = 0;
          this.temp.cost_sgst_rate = 0;
          this.temp.cost_sgst_amount = 0;
          this.temp.cost_amount = this.temp.cost_base;
        } else {
          let gst = (this.temp.cost_gst_rate * this.temp.cost_base) / 100;
          gst = Math.floor(gst);
          this.temp.cost_gst_amount = gst;
          this.temp.cost_cgst_rate = this.temp.cost_gst_rate/2;
          this.temp.cost_cgst_amount = gst/2;
          this.temp.cost_sgst_rate = this.temp.cost_gst_rate/2;
          this.temp.cost_sgst_amount = gst/2;
          this.temp.cost_amount = parseInt(this.temp.cost_base) + gst;
        }
      }
    },
    
    addProduct() {
      this.$refs["productForm"].validate(valid => {
        if (valid) {
          this.loading = true;
          this.buttonLoading = true;
          var form = new FormData();
          let form_data = this.temp;

          for (var key in form_data) {
            if (form_data[key] !== undefined && form_data[key] !== null) {
              form.append(key, form_data[key]);
            }
          }
          form.append('file', this.file);
          createProduct(form).then((response) => {
            this.temp = response.data;
            this.temp.is_color_variant = response.data.is_color_variant == 1 ? true : false;
            this.temp.is_size_variant = response.data.is_size_variant == 1 ? true : false;
            this.loading = false;
            this.buttonLoading = false;
            this.$router.push({ path: '/products/edit', query: { id: response.data.id } });
            this.getAllProductVariant();
            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            });
          }).catch((res) => {
            this.loading = false;
            this.buttonLoading = false;
          });
        }
      });
    },
    updateProduct() {
      this.$refs["productForm"].validate(valid => {
        if (valid) {
          this.loading = true;
          this.buttonLoading = true;
          var form = new FormData();
          let form_data = this.temp;

          for (var key in form_data) {
            if (form_data[key] !== undefined && form_data[key] !== null) {
              form.append(key, form_data[key]);
            }
          }

          form.append('file', this.file);

          updateProduct(form).then((response) => {
            this.temp = response.data;
            this.temp.is_color_variant = response.data.is_color_variant == 1 ? true : false;
            this.temp.is_size_variant = response.data.is_size_variant == 1 ? true : false;
            this.fileList = [];
            this.loading = false;
            this.buttonLoading = false;
            var keys = [];
            response.data.categories.map(cat => {
              keys.push(cat.id);
            })
            keys = keys.filter((item, i, ar) => ar.indexOf(item) === i);
            this.temp.categories = keys;

            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            });
          }).catch((res) => {
            this.buttonLoading = false;
          });
        }
      });
    },
    handleAddImage() {
      this.dialogAddImageVisible = true;
      this.imageFileList = [];
      this.variant = undefined;
      this.order = undefined;
      this.is_fetured = undefined;

    },
    handleAddVariant() {
      this.title = "Add Variant";
      this.resetProductVariant();

      this.dialogStatus = "create";
      this.dialogAddVariantVisible = true;
      this.imageFileList = [];
      this.disabledVariant = false;
      this.product_variant.sku = this.product_code_prefix + '_' + this.temp.product_number.toUpperCase().replace(/\s/g, '');

    },
    addVariant() {
      this.$refs["variantForm"].validate(valid => {
        if (valid) {
          let id = this.$route.query.id
          this.product_variant.product_id = id;
          addProductVariant(this.product_variant).then((response) => {
            this.dialogAddVariantVisible = false;
            this.getAllProductVariantList();
            this.getAllProductVariant();
            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            });
          });
        }
      });
    },
    addImage() {
      if (!this.imageFile) {
        this.$message.error('Please select image');
        return;
      }
      var form = new FormData();
      form.append('id', this.temp.id);
      form.append('variant_id', this.image.variant);
      form.append('order', this.image.order);
      form.append('is_fetured', this.image.is_fetured);
      form.append('file', this.imageFile);
      this.buttonLoading = true;

      uploadProductImage(form).then((response) => {
        this.imageFileList = [];
        this.buttonLoading = false;
        this.dialogAddImageVisible = false;
        this.temp.images.push(response.data);
        this.$notify({
          title: "Success",
          message: response.message,
          type: "success",
          duration: 2000
        });
      });

    },
    deleteImage(row) {
      this.$confirm('Are you sure you want to delete ?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        this.buttonLoading = true;
        deleteProductImage(row.id).then((data) => {
          this.$notify({
            title: "Success",
            message: data.message,
            type: "success",
            duration: 2000
          });
          this.buttonLoading = false;
          const index = this.temp.images.indexOf(row);
          this.temp.images.splice(index, 1);
        });
      });
    },

    handleChange(f, fl) {
      if (fl.length > 1) {
        fl.shift()
      }
      this.file = f.raw
    },
    handleRemove(file, fileList) {
      this.file = undefined;
      this.fileList = [];
    },
    handleExceed(files, fileList) {
      this.$message.warning(`You can not select more than one file, please remove first.`);
    },
    handleImageChange(f, fl) {
      if (fl.length > 1) {
        fl.shift()
      }
      this.imageFile = f.raw
    },
    handleImageRemove(file, fileList) {
      this.imageFile = undefined;
      this.imageFileList = [];
    },
    handleImageExceed(imageFile, imageFileList) {
      this.$message.warning(`You can not select more than one file, please remove first.`);
    },
  }
};

</script>
<style scoped>
.el-checkbox {
  margin-right: 0px;
}

.edit-input {
  padding-right: 100px;
}


.avatar {
  width: 200px;
  height: 115px;
  display: block;
}

.avatar-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 200px;
  height: 115px !important;
  line-height: 115px;
  text-align: center;
}

</style>
