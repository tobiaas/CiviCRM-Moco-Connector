{if $error}
  <div class="messages error">
    <p>{ts}Error:{/ts} {$error}</p>
  </div>
{else}
  <div class="crm-block crm-content-block">
    
    <h3>{ts}Revenue Summary{/ts}</h3>
    <table class="report-layout">
      <tr>
        <td class="label">{ts}Total Invoiced:{/ts}</td>
        <td>€{$mocoData.revenue.total_invoiced|number_format:2}</td>
      </tr>
      <tr>
        <td class="label">{ts}Open Invoices:{/ts}</td>
        <td>€{$mocoData.revenue.open_invoices|number_format:2}</td>
      </tr>
      <tr>
        <td class="label">{ts}Active Projects:{/ts}</td>
        <td>{$mocoData.revenue.active_projects}</td>
      </tr>
      <tr>
        <td class="label">{ts}Project Budgets:{/ts}</td>
        <td>€{$mocoData.revenue.project_budgets|number_format:2}</td>
      </tr>
    </table>
    
    {if $mocoData.projects}
      <h3>{ts}Projects{/ts}</h3>
      <table class="display">
        <thead>
          <tr>
            <th>{ts}Name{/ts}</th>
            <th>{ts}Status{/ts}</th>
            <th>{ts}Budget{/ts}</th>
          </tr>
        </thead>
        <tbody>
          {foreach from=$mocoData.projects item=project}
            <tr>
              <td>{$project.name}</td>
              <td>{if $project.active}{ts}Active{/ts}{else}{ts}Inactive{/ts}{/if}</td>
              <td>€{$project.budget|number_format:2}</td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    {/if}
    
    {if $mocoData.activities}
      <h3>{ts}Recent Activities{/ts}</h3>
      <table class="display">
        <thead>
          <tr>
            <th>{ts}Date{/ts}</th>
            <th>{ts}Description{/ts}</th>
            <th>{ts}Hours{/ts}</th>
          </tr>
        </thead>
        <tbody>
          {foreach from=$mocoData.activities item=activity}
            <tr>
              <td>{$activity.date|crmDate}</td>
              <td>{$activity.description|truncate:50}</td>
              <td>{$activity.hours}</td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    {/if}
    
  </div>
{/if}
