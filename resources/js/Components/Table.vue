<template>
  <table
    ref="Table"
    :class="[
      'wikit',
      'wikit-Table',
      `wikit-Table--linear-${breakpoint}`
    ]"
  >
    <slot />
  </table>
</template>

<script setup lang="ts">
import { PropType, computed, ref } from 'vue';
import { Breakpoint, validateBreakpoint } from '../types/Breakpoint';

/**
 * Tables display categorical information organized across rows and columns in
 * order to facilitate the comparative analysis of data.
 *
 * The WiKit table component provides a wrapper around the common HTML table
 * elements such as `<thead>`, `<tbody>`, `<tr>`, `<th>` and `<td>`, to apply
 * design system styles to tabular data.
 *
 * Adding a `data-header` attribute to the cells allows us to maintain the column
 * headers and display them in the table's linearized form to provide additional
 * context.
 *
 * **Example:**
 *
 * ```html
 * <td data-header="Column Header">Content Here</td>
 * ```
 */
	const Table = ref('Table');
	const props = defineProps({
		linearize: {
			type: String as PropType<Breakpoint>,
			default: Breakpoint.Tablet,
			validator: (value: Breakpoint): boolean => {
				return validateBreakpoint( value );
			}
		}
	});

	const breakpoint = computed<string>(() => {
		return validateBreakpoint( props.linearize ) ? props.linearize : 'tablet';
	});

	defineExpose(Table);

</script>

<style lang="scss">
@import "@wikimedia/codex-design-tokens/theme-wikimedia-ui";

	@mixin linear-table {
		/**
		* Completely removes thead, modern screen readers will expose the
		* generated content
		*/
		thead {
			display: none;
			visibility: hidden;
		}

		/**
		* Make everything display flex for alignment
		*/
		tbody,
		tr,
		th,
		td {
			height: auto;
			display: flex;
			flex-direction: column;
		}

		td,
		th {
			flex-direction: row;
			flex-basis: 60%;
		}

		/**
		* Labeling
		*
		* Adding a data-header attribute to the cells
		* lets us add text before the content to provide
		* the missing context.
		*
		* Markup:
		*   <td data-header="Column Header">Content Here</td>
		*/
		/* stylelint-disable selector-no-qualifying-type  */
		th[data-header]::before,
		td[data-header]::before {
			content: attr(data-header);
			display: block;
			font-weight: 700;
			flex-basis: 40%;

			// Ensure headers stay exactly 40%
			// even if values are wider than 60%
			min-width: 40%;
		}

		th:not([data-header]) {
			font-weight: 700;
		}

		// Hide empty cells
		td:empty {
			display: none;
		}
	}

	.Table {
		/**
		* Layout
		*/
		// As the specificationn state that the table columns should gain their
		// width from cell content (instead of just header content / width) we
		// revert to using the default table layout algorithm. This is done in
		// order to avoid changing the table's display proterty and thus
		// oblitirating it's inehrit accesibility:
		// See: https://www.tpgi.com/short-note-on-what-css-display-properties-do-to-table-semantics/
		table-layout: auto;
		width: 100%;

		/**
		* Borders
		*/
		border-collapse: collapse;

		/**
		* Colors
		*/
		background-color: #fff;
		color: #202122;

		/**
		* Typography
		*/
		font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Lato,Helvetica,Arial,sans-serif;
		font-size: 1em;
		font-weight: 400;

		tr {
			/**
			* Layout
			*/

			/**
			* Borders
			*/
			border-bottom:1px #c8ccd1 solid;
			border-radius: 0;
		}

		tbody tr:hover {
			background-color: $background-color-interactive;
			transition-duration: $transition-duration-medium;
			transition-timing-function: $transition-timing-function-system;
			transition-property: $transition-property-base;
		}

		th,
		td {
			/**
			* Layout
			*/
			padding-inline: .75em;

			/**
			* Typography
			*/
			line-height: 20px;
			text-align: start;
			overflow-wrap: break-word;
			hyphens: auto;
		}

		td {
			/**
			* Layout
			*/
			height: 48px;
			padding-block: .5em;

			/**
			* Typography
			*/
			vertical-align: middle;
		}

		th {
			/**
			* Layout
			*/
			padding-block: .75em;

			/**
			* Typography
			*/
			font-weight: 700;
			vertical-align: top;
		}

		&--linear-mobile {
			@media (max-width: $max-width-breakpoint-mobile) {
				@include linear-table;
			}
		}

		&--linear-tablet {
			@media (max-width: $max-width-breakpoint-tablet) {
				@include linear-table;
			}
		}

		&--linear-desktop {
			@media (max-width: $max-width-breakpoint-desktop) {
				@include linear-table;
			}
		}
	}
</style>