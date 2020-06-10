import Grid from '@material-ui/core/Grid';

const Row = (props) => (
    <Grid container direction="row" justify="space-between" alignItems="center">
    <Grid item xs={3} zeroMinWidth>{props.code}</Grid>
    <Grid item xs={9} zeroMinWidth><p>{props.selection}</p></Grid>
    </Grid>
)

export default Row;