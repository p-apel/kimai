{literal}    
    <script type="text/javascript"> 
        $(document).ready(function() {
            exp_ext_onload();
        }); 
    </script>
{/literal}

<div id="exp_head">
    <div class="left">
    {if $kga.usr}
        <a href="#" onClick="floaterShow('../extensions/ki_expenses/floaters.php','add_edit_record',0,0,600,570); return false;">{$kga.lang.add}</a>
    {/if}
    </div>
    <table>
        <colgroup>
          <col class="options" />
          <col class="date" />
          <col class="time" />
          <col class="value" />
          <col class="knd" />
          <col class="pct" />
          <col class="designation" />
        </colgroup>
        <tbody>
            <tr>
                <td class="option">&nbsp;</td>
                <td class="date">{$kga.lang.datum}</td>
                <td class="time">{$kga.lang.timelabel}</td>
                <td class="value">{$kga.lang.expense}</td>
                <td class="knd">{$kga.lang.knd}</td>
                <td class="pct">{$kga.lang.pct}</td>
                <td class="designation">{$kga.lang.designation}</td>
            </tr>
        </tbody>
    </table>
</div>

<div id="exp">{$exp_display} </div>