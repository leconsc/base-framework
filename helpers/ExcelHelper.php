<?php
/**
 * 导出Excel数据为xml格式工具.
 *
 * @author chenbin
 * @version $Id:ExcelHelper.php, 1.0 2014-04-03 12:30+100 chenbin$
 * @package: WeGames
 * @since 2014-04-03 12:30
 * @copyright 2014(C)Copyright By CQTimes, All rights Reserved.
 */
class ExcelHelper
{
    /** @var array Meta数据 */
    private $_meta = array();
    /** @var array 定义meta信息映射关系 */
    private $_map = array();
    /** @var string */
    private $_cellDefaultStyleId = 's30';

    /**
     * 返回唯一实例.
     *
     * @return ExcelHelper
     */
    public static function &getInstance()
    {
        static $instance;

        if (!$instance instanceof self) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * 初始化数据
     */
    private function __construct()
    {
        $this->_meta['created'] = date('Y-m-d\TH:i:s\Z');
        $this->_meta['lastSaved'] = $this->_meta['created'];
        $this->_meta['company'] = 'CQTimes';

        $this->_map['subject'] = 'title';
        $this->_map['lastAuthor'] = 'author';
    }

    /**
     * 设置Meta值
     *
     * @param $metaName
     * @param $metaValue
     * @return $this
     */
    public function setMetaValue($metaName, $metaValue)
    {
        $this->_meta[$metaName] = $metaValue;
        return $this;
    }

    /**
     * 设置单元格默认Style Id
     *
     * @param $styleId
     */
    public function setCellDefaultStyleId($styleId)
    {
        $this->_cellDefaultStyleId = $styleId;
        return $this;
    }

    /**
     * 输出导出的Header部分.
     *
     * @param $filename
     */
    public function sendDownloadHeader($filename)
    {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");

        //防止导出中文名称出现乱码
        $ua = $_SERVER["HTTP_USER_AGENT"];
        header('Content-Type:application/octet-stream');
        if (preg_match("/msie/i", $ua)) {
            $encoded_filename = urlencode($filename);
            $encoded_filename = str_replace("+", "%20", $encoded_filename);
            header('Content-Disposition:attachment;filename="' . $encoded_filename . '.xml"');
        } else {
            header('Content-Disposition:attachment;filename="' . $filename . '.xml"');
        }
        header("Content-Transfer-Encoding: binary");
    }

    /**
     * 转换列为字母弃于列
     */
    public function writeHeader()
    {
        foreach ($this->_map as $metaName => $replaceMetaName) {
            if (isset($this->_meta[$replaceMetaName]) && !isset($this->_meta[$metaName])) {
                $this->_meta[$metaName] = $this->_meta[$replaceMetaName];
            }
        }

        $search = array();
        $replace = array();
        foreach ($this->_meta as $metaName => $metaValue) {
            $search[] = '{' . $metaName . '}';
            $replace[] = $metaValue;
        }

        $header = str_replace($search, $replace, EXCEL_HEADER);
        echo $header;
    }

    /**
     * 写工作表开始部分
     */
    public function writeWorkSheetStart($sheetTitle, $columnNums)
    {
        $content = <<<EOF
        <Worksheet ss:Name="{$sheetTitle}">
        <Table ss:ExpandedColumnCount="{$columnNums}" x:FullColumns="1" x:FullRows="1">

EOF;
        echo $content;
    }

    /**
     * 写工作表结束部分
     */
    public function writeWorkSheetEnd()
    {
        echo EXCEL_END;
    }

    /**
     * 定义列宽.
     *
     * @param $width
     * @param null $span
     */
    public function writeColumnWidth($width, $span = null)
    {
        $str = '<Column ss:Width="' . $width . '"';
        if ($span) {
            $str .= ' ss:Span="' . $span . '"';
        }
        $str .= "/>\n";
        echo $str;
    }

    /**
     * 写工作表文字标题
     */
    public function writeCell($content, $type = 'String', $styleId = null)
    {
        if (empty($styleId)) {
            $styleId = $this->_cellDefaultStyleId;
        }
        $cellString = sprintf('<Cell ss:StyleID="%s"><Data ss:Type="%s">%s</Data></Cell>' . "\n", $styleId, $type, $content);
        echo $cellString;
    }

    /**
     * 打印行开始
     */
    public function writeRowStart()
    {
        echo "<Row>\n";
    }

    /**
     * 打印行结束
     */
    public function writeRowEnd()
    {
        echo "</Row>\n";
    }

    /**
     * 写表数值
     */
    public function writeNumberCell($content, $styleId = null)
    {
        $this->writeCell($content, 'Number', $styleId);
    }

    /**
     * 写表字符串值
     */
    public function writeStringCell($content, $styleId = null)
    {
        $this->writeCell($content, 'String', $styleId);
    }

    /**
     * 写合并列
     */
    public function writeMerge($mergeAcross, $content, $styleId = 's634')
    {
        if (empty($styleId)) {
            $styleId = $this->_cellDefaultStyleId;
        }
        $cellString = sprintf('<Row><Cell ss:MergeAcross="%s" ss:StyleID="%s"><Data ss:Type="String">%s</Data></Cell></Row>',
            $mergeAcross, $styleId, $content);
        echo $cellString;
    }
}

//定义Excel头部
define('EXCEL_HEADER', '<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
          xmlns:o="urn:schemas-microsoft-com:office:office"
          xmlns:x="urn:schemas-microsoft-com:office:excel"
          xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
          xmlns:html="http://www.w3.org/TR/REC-html40">
    <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
        <Title>{title}</Title>
        <Subject>{subject}</Subject>
        <Author>{author}</Author>
        <LastAuthor>{lastAuthor}</LastAuthor>
        <Created>{created}</Created>
        <LastSaved>{lastSaved}</LastSaved>
        <Company>{company}</Company>
        <Version>12.00</Version>
    </DocumentProperties>
    <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
        <WindowHeight>8550</WindowHeight>
        <WindowWidth>21255</WindowWidth>
        <WindowTopX>180</WindowTopX>
        <WindowTopY>540</WindowTopY>
        <ProtectStructure>False</ProtectStructure>
        <ProtectWindows>False</ProtectWindows>
    </ExcelWorkbook>
    <Styles>
        <Style ss:ID="Default" ss:Name="Normal">
            <Alignment ss:Vertical="Bottom"/>
            <Borders/>
            <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>
            <Interior/>
            <NumberFormat/>
            <Protection/>
        </Style>
        <Style ss:ID="s16">
            <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#333333"/>
            </Borders>
            <Font ss:FontName="宋体" x:CharSet="134" ss:Size="12" ss:Color="#000000" ss:Bold="1"/>
        </Style>
        <Style ss:ID="s30">
            <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#333333"/>
            </Borders>
            <Font ss:FontName="宋体" x:CharSet="134" ss:Size="12" ss:Color="#000000"/>
            <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
        </Style>
        <Style ss:ID="s44">
            <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#333333"/>
            </Borders>
            <Font ss:FontName="宋体" x:CharSet="134" ss:Size="12" ss:Color="#000000"/>
            <Interior ss:Color="#DFDFDF" ss:Pattern="Solid"/>
        </Style>
        <Style ss:ID="s634">
            <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#333333"/>
            </Borders>
            <Font ss:FontName="宋体" x:CharSet="134" ss:Size="18" ss:Color="#000000" ss:Bold="1"/>
            <Interior ss:Color="#A66103" ss:Pattern="Solid"/>
        </Style>
        <Style ss:ID="s635">
            <Alignment ss:Horizontal="Right" ss:Vertical="Bottom"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#333333"/>
            </Borders>
            <Font ss:FontName="宋体" x:CharSet="134" ss:Size="12" ss:Color="#000000" ss:Bold="1"/>
        </Style>
    </Styles>
');

define('EXCEL_END', '</Table>
        <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
            <PageSetup>
                <Header x:Margin="0.3"/>
                <Footer x:Margin="0.3"/>
                <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
            </PageSetup>
            <Print>
                <ValidPrinterInfo/>
                <HorizontalResolution>600</HorizontalResolution>
                <VerticalResolution>600</VerticalResolution>
            </Print>
            <Selected/>
            <ProtectObjects>False</ProtectObjects>
            <ProtectScenarios>False</ProtectScenarios>
            <AllowFormatCells/>
            <AllowSizeCols/>
            <AllowSizeRows/>
            <AllowInsertCols/>
            <AllowInsertRows/>
            <AllowInsertHyperlinks/>
            <AllowDeleteCols/>
            <AllowDeleteRows/>
            <AllowSort/>
            <AllowFilter/>
            <AllowUsePivotTables/>
        </WorksheetOptions>
    </Worksheet>
</Workbook>
');